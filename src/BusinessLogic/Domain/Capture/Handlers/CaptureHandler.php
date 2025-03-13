<?php

namespace Adyen\Core\BusinessLogic\Domain\Capture\Handlers;

use Adyen\Core\BusinessLogic\Domain\Capture\Models\CaptureRequest;
use Adyen\Core\BusinessLogic\Domain\Capture\Proxies\CaptureProxy;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionSettings;
use Adyen\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Capture\FailedCaptureRequestEvent;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Capture\SuccessfulCaptureRequestEvent;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\ShopEvents;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Services\ShopNotificationService;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Exceptions\InvalidMerchantReferenceException;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models\HistoryItem;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Services\TransactionDetailsService;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Services\TransactionHistoryService;
use Adyen\Core\Infrastructure\Utility\TimeProvider;
use Adyen\Webhook\EventCodes;
use DateTimeInterface;
use Exception;

/**
 * Class CaptureHandler
 *
 * @package Adyen\Core\BusinessLogic\Domain\Capture\Handlers
 */
class CaptureHandler
{
    /**
     * @var TransactionHistoryService
     */
    private $transactionHistoryService;

    /**
     * @var ShopNotificationService
     */
    private $shopNotificationService;

    /**
     * @var CaptureProxy
     */
    private $captureProxy;

    /**
     * @var ConnectionService
     */
    private $connectionService;
    /**
     * @var TransactionDetailsService
     */
    private $transactionDetailsService;

    /**
     * @param TransactionHistoryService $transactionHistoryService
     * @param ShopNotificationService $shopNotificationService
     * @param CaptureProxy $captureProxy
     * @param ConnectionService $connectionService
     * @param TransactionDetailsService $transactionDetailsService
     */
    public function __construct(
        TransactionHistoryService $transactionHistoryService,
        ShopNotificationService   $shopNotificationService,
        CaptureProxy              $captureProxy,
        ConnectionService         $connectionService,
        TransactionDetailsService $transactionDetailsService
    )
    {
        $this->transactionHistoryService = $transactionHistoryService;
        $this->shopNotificationService = $shopNotificationService;
        $this->captureProxy = $captureProxy;
        $this->connectionService = $connectionService;
        $this->transactionDetailsService = $transactionDetailsService;
    }

    /**
     * Handles capture request.
     *
     * @param string $merchantReference
     * @param Amount $amount
     *
     * @return bool
     *
     * @throws InvalidMerchantReferenceException
     */
    public function handle(string $merchantReference, Amount $amount, string $pspReference = ''): bool
    {
        $transactionHistory = $this->transactionHistoryService->getTransactionHistory($merchantReference);

        try {
            if (empty($pspReference) &&
                $transactionHistory->collection()->filterAllByEventCode(EventCodes::ORDER_OPENED)->isEmpty()) {
                $pspReference = $transactionHistory->getOriginalPspReference();
            }

            $connectionSettings = $this->connectionService->getConnectionData();
            $merchantAccount = $connectionSettings ?
                $connectionSettings->getActiveConnectionData()->getMerchantId() : '';

            if ($pspReference) {
                return $this->capturePayment($merchantAccount, $pspReference, $amount, $transactionHistory);
            }

            $success = true;

            foreach ($transactionHistory->collection()->filterAllByEventCode(EventCodes::AUTHORISATION)->getAll() as $item) {
                if ($amount->getValue() === 0) {
                    break;
                }

                $samePaymentItems = $transactionHistory->collection()->filterByOriginalReference($item->getPspReference());

                if (!$samePaymentItems->filterAllByEventCode(EventCodes::CANCELLATION)->isEmpty() ||
                    $samePaymentItems->filterAllByEventCode(EventCodes::REFUND)->isEmpty()) {
                    continue;
                }

                $captureAmount = $this->transactionDetailsService->getCapturedAmount($transactionHistory, $samePaymentItems);
                $capturableAmount = $this->transactionDetailsService->getCapturableAmount($transactionHistory, $samePaymentItems, $captureAmount);

                if ($capturableAmount->getValue() < $amount->getValue()) {
                    $success &= $this->capturePayment($merchantAccount, $item->getPspReference(), $capturableAmount, $transactionHistory);
                    $amount->minus($capturableAmount);
                } else {
                    $success &= $this->capturePayment($merchantAccount, $item->getPspReference(), $amount, $transactionHistory);
                    $amount = Amount::fromInt(0, $amount->getCurrency());
                }
            }

            return $success;
        } catch (Exception $exception) {
            $this->addHistoryItem($transactionHistory, $amount, false, $pspReference);
            $this->pushNotification(false, $transactionHistory);

            throw $exception;
        }
    }

    /**
     * @param string $merchantAccount
     * @param string $pspReference
     * @param Amount $amount
     * @param TransactionHistory $transactionHistory
     *
     * @return bool
     *
     * @throws InvalidMerchantReferenceException
     */
    private function capturePayment(string $merchantAccount, string $pspReference, Amount $amount, TransactionHistory $transactionHistory): bool
    {
        $success = $this->captureProxy->capturePayment(
            new CaptureRequest($pspReference, $amount, $merchantAccount)
        );

        $this->addHistoryItem($transactionHistory, $amount, $success, $pspReference);
        $this->pushNotification($success, $transactionHistory);

        return $success;
    }

    /**
     * Adds new history item to collection.
     *
     * @param TransactionHistory $history
     * @param Amount $amount
     * @param bool $success
     * @param string $pspReference
     * @return void
     *
     * @throws InvalidMerchantReferenceException
     */
    private function addHistoryItem(TransactionHistory $history, Amount $amount, bool $success, string $pspReference): void
    {
        $lastItem = $history->collection()->last();
        $captureRequestCount = count(
            $history->collection()->filterByEventCode(ShopEvents::CAPTURE_REQUEST)->getAll()
        );
        $history->add(
            new HistoryItem(
                'capture' . ++$captureRequestCount . '_' . $pspReference,
                $history->getMerchantReference(),
                ShopEvents::CAPTURE_REQUEST,
                $lastItem ? $lastItem->getPaymentState() : '',
                TimeProvider::getInstance()->getCurrentLocalTime()->format(DateTimeInterface::ATOM),
                $success,
                $amount,
                $history->getPaymentMethod(),
                $history->getRiskScore(),
                $history->isLive()
            )
        );

        $this->transactionHistoryService->setTransactionHistory($history);
    }

    /**
     * Push shop notification.
     *
     * @param bool $success
     * @param TransactionHistory $history
     *
     * @return void
     */
    private function pushNotification(bool $success, TransactionHistory $history): void
    {
        if ($success) {
            $this->shopNotificationService->pushNotification(
                new SuccessfulCaptureRequestEvent($history->getMerchantReference(), $history->getPaymentMethod())
            );

            return;
        }

        $this->shopNotificationService->pushNotification(
            new FailedCaptureRequestEvent($history->getMerchantReference(), $history->getPaymentMethod())
        );
    }
}
