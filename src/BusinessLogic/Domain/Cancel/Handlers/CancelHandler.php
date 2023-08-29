<?php

namespace Adyen\Core\BusinessLogic\Domain\Cancel\Handlers;

use Adyen\Core\BusinessLogic\Domain\Cancel\Models\CancelRequest;
use Adyen\Core\BusinessLogic\Domain\Cancel\Proxies\CancelProxy;
use Adyen\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
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
     * @param TransactionHistoryService $transactionHistoryService
     * @param ShopNotificationService $shopNotificationService
     * @param CancelProxy $captureProxy
     * @param ConnectionService $connectionService
     */
    public function __construct(
        TransactionHistoryService $transactionHistoryService,
        ShopNotificationService $shopNotificationService,
        CancelProxy $captureProxy,
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
     *
     * @return bool
     *
     * @throws InvalidMerchantReferenceException
     */
    public function handle(string $merchantReference): bool
    {
        $transactionHistory = $this->transactionHistoryService->getTransactionHistory($merchantReference);

        try {
            $pspReference = $transactionHistory->getOriginalPspReference();
            $connectionSettings = $this->connectionService->getConnectionData();
            $merchantAccount = $connectionSettings ? $connectionSettings->getActiveConnectionData()->getMerchantId(
            ) : '';
            $success = $this->captureProxy->cancelPayment(
                new CancelRequest($pspReference, $merchantReference, $merchantAccount)
            );

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
