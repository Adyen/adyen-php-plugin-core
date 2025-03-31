<?php

namespace Adyen\Core\BusinessLogic\Domain\Refund\Handlers;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use Adyen\Core\BusinessLogic\Domain\Refund\Models\RefundRequest;
use Adyen\Core\BusinessLogic\Domain\Refund\Proxies\RefundProxy;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Refund\FailedRefundRequestEvent;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Refund\SuccessfulRefundRequestEvent;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\ShopEvents;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Services\ShopNotificationService;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Exceptions\InvalidMerchantReferenceException;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models\HistoryItem;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Services\TransactionDetailsService;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Services\TransactionHistoryService;
use Adyen\Core\Infrastructure\Exceptions\BaseException;
use Adyen\Core\Infrastructure\Utility\TimeProvider;
use Adyen\Webhook\EventCodes;
use DateTimeInterface;
use Exception;

/**
 * Class RefundHandler
 *
 * @package Adyen\Core\BusinessLogic\Domain\Refund\Handlers
 */
class RefundHandler
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
     * @var RefundProxy
     */
    private $refundProxy;

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
     * @param RefundProxy $refundProxy
     * @param ConnectionService $connectionService
     * @param TransactionDetailsService $transactionDetailsService
     */
    public function __construct(
        TransactionHistoryService $transactionHistoryService,
        ShopNotificationService $shopNotificationService,
        RefundProxy $refundProxy,
        ConnectionService $connectionService,
        TransactionDetailsService $transactionDetailsService
    ) {
        $this->transactionHistoryService = $transactionHistoryService;
        $this->shopNotificationService = $shopNotificationService;
        $this->refundProxy = $refundProxy;
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
            $merchantAccount = $connectionSettings ? $connectionSettings->getActiveConnectionData()->getMerchantId() : '';

            if ($pspReference) {
                return $this->refund($pspReference, $amount, $merchantAccount, $transactionHistory);
            }

            $success = true;
            $refundRequests = [];

            $authorizationItems = $transactionHistory->collection()
                ->filterAllByEventCode(EventCodes::AUTHORISATION)
                ->filterAllByStatus(true)
                ->getAll();
            foreach ($authorizationItems as $item) {
                if ($amount->getValue() === 0) {
                    break;
                }

                $samePaymentItems = $transactionHistory->collection()->filterByOriginalReference($item->getPspReference());

                $refundedAmount = $samePaymentItems->filterByEventCode(EventCodes::REFUND)->getAmount($transactionHistory->getCurrency());
                $captureAmount = $this->transactionDetailsService->getCapturedAmount($transactionHistory, $samePaymentItems);
                $cancelledAmount = $samePaymentItems->filterByEventCode(EventCodes::CANCELLATION)->getAmount($transactionHistory->getCurrency());
                $refund = $this->transactionDetailsService->isRefundSupported($item->getPaymentMethod(), $refundedAmount, $captureAmount,
                        $cancelledAmount);
                $partialRefund = $this->transactionDetailsService->isPartialRefundSupported(
                        $item->getPaymentMethod(),
                        $refundedAmount,
                        $captureAmount,
                        $cancelledAmount
                    );
                $refundableAmount = $captureAmount->minus($refundedAmount);

                if (!$refund || $refundableAmount->getValue() <= 0) {
                    continue;
                }

                if ($refundableAmount->getValue() < $amount->getValue()) {
                    if (!$partialRefund && $refundableAmount->getValue() < $captureAmount->getValue()) {
                        continue;
                    }

                    $refundRequests[] = [
                        'pspReference' => $item->getPspReference(),
                        'refundableAmount' => $refundableAmount
                    ];
                    $amount = $amount->minus($refundableAmount);
                } else {
                    if (!$partialRefund && $refundableAmount->getValue() > $amount->getValue()) {
                        continue;
                    }

                    $refundRequests[] = [
                        'pspReference' => $item->getPspReference(),
                        'refundableAmount' => $amount
                    ];
                    $amount = Amount::fromInt(0, $amount->getCurrency());
                }
            }

            if ($amount->getValue() > 0) {
                throw new BaseException('Refund failed.');
            }

            foreach ($refundRequests as $refundRequest) {
                $success &= $this->refund(
                    $refundRequest['pspReference'],
                    $refundRequest['refundableAmount'],
                    $merchantAccount,
                    $transactionHistory
                );
            }

            return $success;
        } catch (Exception $exception) {
            $this->addHistoryItem($transactionHistory, $amount, false, $pspReference);
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
    private function addHistoryItem(TransactionHistory $history, Amount $amount, bool $success, string $pspReference): void
    {
        $lastItem = $history->collection()->last();
        $refundRequestCount = count(
            $history->collection()->filterByEventCode(ShopEvents::REFUND_REQUEST)->getAll()
        );
        $history->add(
            new HistoryItem(
                'refund' . ++$refundRequestCount . '_' . $pspReference,
                $history->getMerchantReference(),
                ShopEvents::REFUND_REQUEST,
                $lastItem ? $lastItem->getPaymentState() : '',
                TimeProvider::getInstance()->getCurrentLocalTime()->format(DateTimeInterface::ATOM),
                $success,
                $amount,
                $history->getPaymentMethod(),
                $history->getRiskScore(),
                $history->isLive(),
                $pspReference
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
                new SuccessfulRefundRequestEvent($history->getMerchantReference(), $history->getPaymentMethod())
            );

            return;
        }

        $this->shopNotificationService->pushNotification(
            new FailedRefundRequestEvent($history->getMerchantReference(), $history->getPaymentMethod())
        );
    }

    /**
     * @param string $pspReference
     * @param Amount $amount
     * @param string $merchantAccount
     * @param TransactionHistory $transactionHistory
     * @return bool
     * @throws InvalidMerchantReferenceException
     */
    public function refund(string $pspReference, Amount $amount, string $merchantAccount, TransactionHistory $transactionHistory): bool
    {
        $success = $this->refundProxy->refundPayment(new RefundRequest($pspReference, $amount, $merchantAccount));
        $this->addHistoryItem($transactionHistory, $amount, $success, $pspReference);
        $this->pushNotification($success, $transactionHistory);
        return $success;
    }
}
