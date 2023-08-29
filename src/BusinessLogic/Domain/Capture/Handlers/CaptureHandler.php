<?php

namespace Adyen\Core\BusinessLogic\Domain\Capture\Handlers;

use Adyen\Core\BusinessLogic\Domain\Capture\Models\CaptureRequest;
use Adyen\Core\BusinessLogic\Domain\Capture\Proxies\CaptureProxy;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Capture\FailedCaptureRequestEvent;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Capture\SuccessfulCaptureRequestEvent;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\ShopEvents;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Services\ShopNotificationService;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Exceptions\InvalidMerchantReferenceException;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models\HistoryItem;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Services\TransactionHistoryService;
use Adyen\Core\Infrastructure\Utility\TimeProvider;
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
     * @param TransactionHistoryService $transactionHistoryService
     * @param ShopNotificationService $shopNotificationService
     * @param CaptureProxy $captureProxy
     * @param ConnectionService $connectionService
     */
    public function __construct(
        TransactionHistoryService $transactionHistoryService,
        ShopNotificationService $shopNotificationService,
        CaptureProxy $captureProxy,
        ConnectionService $connectionService
    ) {
        $this->transactionHistoryService = $transactionHistoryService;
        $this->shopNotificationService = $shopNotificationService;
        $this->captureProxy = $captureProxy;
        $this->connectionService = $connectionService;
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
    public function handle(string $merchantReference, Amount $amount): bool
    {
        $transactionHistory = $this->transactionHistoryService->getTransactionHistory($merchantReference);

        try {
            $pspReference = $transactionHistory->getOriginalPspReference();
            $connectionSettings = $this->connectionService->getConnectionData();
            $merchantAccount = $connectionSettings ? $connectionSettings->getActiveConnectionData()->getMerchantId(
            ) : '';
            $success = $this->captureProxy->capturePayment(
                new CaptureRequest($pspReference, $amount, $merchantAccount)
            );

            $this->addHistoryItem($transactionHistory, $amount, $success);
            $this->pushNotification($success, $transactionHistory);

            return $success;
        } catch (Exception $exception) {
            $this->addHistoryItem($transactionHistory, $amount, false);
            $this->pushNotification(false, $transactionHistory);

            throw $exception;
        }
    }

    /**
     * Adds new history item to collection.
     *
     * @param TransactionHistory $history
     * @param Amount $amount
     * @param bool $success
     *
     * @return void
     *
     * @throws InvalidMerchantReferenceException
     */
    private function addHistoryItem(TransactionHistory $history, Amount $amount, bool $success): void
    {
        $lastItem = $history->collection()->last();
        $captureRequestCount = count(
            $history->collection()->filterByEventCode(ShopEvents::CAPTURE_REQUEST)->getAll()
        );
        $history->add(
            new HistoryItem(
                'capture' . ++$captureRequestCount . '_' . $history->getOriginalPspReference(),
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
