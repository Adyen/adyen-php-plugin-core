<?php

namespace Adyen\Core\BusinessLogic\Domain\Cancel\Handlers;

use Adyen\Core\BusinessLogic\Domain\Cancel\Models\CancelRequest;
use Adyen\Core\BusinessLogic\Domain\Cancel\Proxies\CancelProxy;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\CurrencyMismatchException;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\InvalidCurrencyCode;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Currency;
use Adyen\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use Adyen\Core\BusinessLogic\Domain\Refund\Models\RefundRequest;
use Adyen\Core\BusinessLogic\Domain\Refund\Proxies\RefundProxy;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Cancellation\FailedCancellationRequestEvent;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Cancellation\SuccessfulCancellationRequestEvent;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\ShopEvents;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Services\ShopNotificationService;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Exceptions\InvalidMerchantReferenceException;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Services\TransactionDetailsService;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Services\TransactionHistoryService;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models\HistoryItem;
use Adyen\Core\Infrastructure\Utility\TimeProvider;
use Adyen\Webhook\EventCodes;
use DateTimeInterface;
use Exception;

/**
 * Class CancelHandler
 *
 * @package Adyen\Core\BusinessLogic\Domain\Cancel\Handlers
 */
class CancelHandler
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
     * @var CancelProxy
     */
    private $cancelProxy;

    /**
     * @var ConnectionService
     */
    private $connectionService;
    /**
     * @var RefundProxy
     */
    private $refundProxy;
    /**
     * @var TransactionDetailsService
     */
    private $transactionDetailsService;

    /**
     * @param TransactionHistoryService $transactionHistoryService
     * @param ShopNotificationService $shopNotificationService
     * @param CancelProxy $captureProxy
     * @param ConnectionService $connectionService
     * @param RefundProxy $refundProxy
     * @param TransactionDetailsService $transactionDetailsService
     */
    public function __construct(
        TransactionHistoryService $transactionHistoryService,
        ShopNotificationService   $shopNotificationService,
        CancelProxy               $captureProxy,
        ConnectionService         $connectionService,
        RefundProxy               $refundProxy,
        TransactionDetailsService $transactionDetailsService
    )
    {
        $this->transactionHistoryService = $transactionHistoryService;
        $this->shopNotificationService = $shopNotificationService;
        $this->cancelProxy = $captureProxy;
        $this->connectionService = $connectionService;
        $this->refundProxy = $refundProxy;
        $this->transactionDetailsService = $transactionDetailsService;
    }

    /**
     * Handles capture request.
     *
     * @param string $merchantReference
     *
     * @return bool
     *
     * @throws InvalidMerchantReferenceException
     */
    public function handle(string $merchantReference): bool
    {
        $transactionHistory = $this->transactionHistoryService->getTransactionHistory($merchantReference);

        try {
            return $this->cancel($transactionHistory, $merchantReference);
        } catch (Exception $exception) {
            $this->addHistoryItem($transactionHistory, false, ShopEvents::CANCELLATION_REQUEST, 'cancel');
            $this->pushNotification(false, $transactionHistory);

            throw $exception;
        }
    }

    /**
     * @param TransactionHistory $transactionHistory
     * @param string $merchantReference
     *
     * @return bool
     *
     * @throws CurrencyMismatchException
     * @throws InvalidCurrencyCode
     * @throws InvalidMerchantReferenceException
     */
    private function cancel(TransactionHistory $transactionHistory, string $merchantReference): bool
    {
        $connectionSettings = $this->connectionService->getConnectionData();
        $merchantAccount = $connectionSettings ? $connectionSettings->getActiveConnectionData()->getMerchantId() : '';

        if ($transactionHistory->getOrderPspReference()) {
            $success = true;

            foreach ($transactionHistory->getAuthorizationPspReferences() as $authorizationPspReference) {
                $success &= $this->cancelOrRefund($transactionHistory, $authorizationPspReference, $merchantAccount);
            }

            return $success;
        }

        $pspReference = $transactionHistory->getOriginalPspReference();

        $success = $this->cancelProxy->cancelPayment(
            new CancelRequest($pspReference, $merchantReference, $merchantAccount)
        );
        $this->addHistoryItem($transactionHistory, $success, ShopEvents::CANCELLATION_REQUEST, 'cancel');
        $this->pushNotification($success, $transactionHistory);

        return $success;
    }

    /**
     * @throws CurrencyMismatchException
     * @throws InvalidMerchantReferenceException
     * @throws InvalidCurrencyCode
     */
    private function cancelOrRefund(TransactionHistory $transactionHistory, string $authorizationPspReference, string $merchantAccount): bool
    {
        $authorization = $transactionHistory->collection()->filterAllByPspReference($authorizationPspReference)->first();

        if (!$authorization) {
            return true;
        }

        $cancelled = $transactionHistory->collection()->filterByOriginalReference($authorizationPspReference)->filterAllByEventCode(EventCodes::CANCELLATION);

        if (!$cancelled->isEmpty()) {
            return true;
        }

        $refunded = $transactionHistory->collection()->filterByOriginalReference($authorizationPspReference)->filterAllByEventCode(EventCodes::REFUND);
        $refundedAmount = $refunded->getAmount(Currency::fromIsoCode($transactionHistory->getCurrency()));

        if ($authorization->getAmount()->getValue() === $refundedAmount->getValue()) {
            return true;
        }

        $capturedAmount = $this->transactionDetailsService->getCapturedAmount(
            $transactionHistory,
            $transactionHistory->collection()->filterByOriginalReference($authorizationPspReference)
        );

        $refund = true;
        $cancellation = true;

        if ($capturedAmount->getValue() > 0) {
            $refund = $this->refundProxy->refundPayment(new RefundRequest($authorizationPspReference, $capturedAmount, $merchantAccount));
            $this->addHistoryItem($transactionHistory, $refund, ShopEvents::REFUND_REQUEST, 'refund');
            $this->pushNotification($refund, $transactionHistory);
        }

        if ($capturedAmount->getValue() < $authorization->getAmount()->getValue()) {
            $cancellation = $this->cancelProxy->cancelPayment(new CancelRequest($authorizationPspReference, $transactionHistory->getMerchantReference(), $merchantAccount));
            $this->addHistoryItem($transactionHistory, $cancellation, ShopEvents::CANCELLATION_REQUEST, 'cancel');
            $this->pushNotification($cancellation, $transactionHistory);
        }

        return $cancellation && $refund;
    }

    /**
     * Adds new history item to collection.
     *
     * @param TransactionHistory $history
     * @param bool $success
     *
     * @return void
     *
     * @throws InvalidMerchantReferenceException
     */
    private function addHistoryItem(TransactionHistory $history, bool $success, string $statusCode, string $action): void
    {
        $lastItem = $history->collection()->last();
        $cancelRequestCount = count(
            $history->collection()->filterByEventCode($statusCode)->getAll()
        );
        $history->add(
            new HistoryItem(
                $action . ++$cancelRequestCount . '_' . $history->getOriginalPspReference(),
                $history->getMerchantReference(),
                $statusCode,
                $lastItem ? $lastItem->getPaymentState() : '',
                TimeProvider::getInstance()->getCurrentLocalTime()->format(DateTimeInterface::ATOM),
                $success,
                $lastItem->getAmount(),
                $history->getPaymentMethod(),
                $history->getRiskScore(),
                $history->isLive(),
                $history->getOriginalPspReference()
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
                new SuccessfulCancellationRequestEvent(
                    $history->getMerchantReference(),
                    $history->getPaymentMethod()
                )
            );

            return;
        }

        $this->shopNotificationService->pushNotification(
            new FailedCancellationRequestEvent($history->getMerchantReference(), $history->getPaymentMethod())
        );
    }
}
