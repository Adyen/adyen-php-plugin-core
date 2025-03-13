<?php

namespace Adyen\Core\BusinessLogic\Domain\Cancel\Handlers;

use Adyen\Core\BusinessLogic\Domain\Cancel\Models\CancelRequest;
use Adyen\Core\BusinessLogic\Domain\Cancel\Proxies\CancelProxy;
use Adyen\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use Adyen\Core\BusinessLogic\Domain\PartialPayments\Service\PartialPaymentService;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Cancellation\FailedCancellationRequestEvent;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Cancellation\SuccessfulCancellationRequestEvent;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\ShopEvents;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Services\ShopNotificationService;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Exceptions\InvalidMerchantReferenceException;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Services\TransactionHistoryService;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models\HistoryItem;
use Adyen\Core\Infrastructure\Utility\TimeProvider;
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
    private $captureProxy;

    /**
     * @var ConnectionService
     */
    private $connectionService;
    /**
     * @var PartialPaymentService
     */
    private $partialService;

    /**
     * @param TransactionHistoryService $transactionHistoryService
     * @param ShopNotificationService $shopNotificationService
     * @param CancelProxy $captureProxy
     * @param ConnectionService $connectionService
     * @param PartialPaymentService $partialService
     */
    public function __construct(
        TransactionHistoryService $transactionHistoryService,
        ShopNotificationService $shopNotificationService,
        CancelProxy $captureProxy,
        ConnectionService $connectionService,
        PartialPaymentService $partialService
    ) {
        $this->transactionHistoryService = $transactionHistoryService;
        $this->shopNotificationService = $shopNotificationService;
        $this->captureProxy = $captureProxy;
        $this->connectionService = $connectionService;
        $this->partialService = $partialService;
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
            $success = $this->cancel($transactionHistory, $merchantReference);

            $this->addHistoryItem($transactionHistory, $success);
            $this->pushNotification($success, $transactionHistory);

            return $success;
        } catch (Exception $exception) {
            $this->addHistoryItem($transactionHistory, false);
            $this->pushNotification(false, $transactionHistory);

            throw $exception;
        }
    }

    /**
     * @param TransactionHistory $transactionHistory
     * @param string $merchantReference
     *
     * @return bool
     */
    private function cancel(TransactionHistory $transactionHistory, string $merchantReference): bool
    {
        if ($transactionHistory->getOrderPspReference()) {
            return strtolower($this->partialService->cancelOrder(
                $transactionHistory->getOrderPspReference(), $transactionHistory->getOrderData()
            )->getResultCode()) === 'received';
        }

        $pspReference = $transactionHistory->getOriginalPspReference();
        $connectionSettings = $this->connectionService->getConnectionData();
        $merchantAccount = $connectionSettings ? $connectionSettings->getActiveConnectionData()->getMerchantId(
        ) : '';
        return $this->captureProxy->cancelPayment(
            new CancelRequest($pspReference, $merchantReference, $merchantAccount)
        );
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
    private function addHistoryItem(TransactionHistory $history, bool $success): void
    {
        $lastItem = $history->collection()->last();
        $cancelRequestCount = count(
            $history->collection()->filterByEventCode(ShopEvents::CANCELLATION_REQUEST)->getAll()
        );
        $history->add(
            new HistoryItem(
                'cancel' . ++$cancelRequestCount . '_' . $history->getOriginalPspReference(),
                $history->getMerchantReference(),
                ShopEvents::CANCELLATION_REQUEST,
                $lastItem ? $lastItem->getPaymentState() : '',
                TimeProvider::getInstance()->getCurrentLocalTime()->format(DateTimeInterface::ATOM),
                $success,
                $lastItem->getAmount(),
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
