<?php

namespace Adyen\Core\BusinessLogic\Domain\Webhook\Services;

use Adyen\Core\BusinessLogic\Domain\Integration\Order\OrderService;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Exceptions\InvalidMerchantReferenceException;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models\HistoryItem;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Services\TransactionHistoryService;
use Adyen\Core\BusinessLogic\Domain\Webhook\Models\Webhook;

/**
 * Class WebhookSynchronizationService
 *
 * @package Adyen\Core\BusinessLogic\Webhook\Services
 */
class WebhookSynchronizationService
{
    /**
     * @var TransactionHistoryService
     */
    protected $transactionHistoryService;

    /**
     * @var OrderService
     */
    protected $orderService;

    /**
     * @var OrderStatusProvider
     */
    protected $orderStatusProvider;

    /**
     * @param TransactionHistoryService $transactionHistoryService
     * @param OrderService $orderService
     * @param OrderStatusProvider $orderStatusProvider
     */
    public function __construct(
        TransactionHistoryService $transactionHistoryService,
        OrderService $orderService,
        OrderStatusProvider $orderStatusProvider
    ) {
        $this->transactionHistoryService = $transactionHistoryService;
        $this->orderService = $orderService;
        $this->orderStatusProvider = $orderStatusProvider;
    }

    /**
     * @param Webhook $webhook
     *
     * @return bool
     *
     * @throws InvalidMerchantReferenceException
     */
    public function isSynchronizationNeeded(Webhook $webhook): bool
    {
        return !$this->hasDuplicates(
            $this->transactionHistoryService->getTransactionHistory($webhook->getMerchantReference()),
            $webhook
        );
    }

    /**
     * @param Webhook $webhook
     *
     * @return void
     *
     * @throws InvalidMerchantReferenceException
     */
    public function synchronizeChanges(Webhook $webhook): void
    {
        $transactionHistory = $this->transactionHistoryService->getTransactionHistory($webhook->getMerchantReference());
        $newState = $this->orderStatusProvider->getNewPaymentState($webhook, $transactionHistory);

        $transactionHistory->add(
            new HistoryItem(
                $webhook->getPspReference(),
                $webhook->getMerchantReference(),
                $webhook->getEventCode(),
                $newState,
                $webhook->getEventDate(),
                $webhook->isSuccess(),
                $webhook->getAmount(),
                $webhook->getPaymentMethod(),
                $webhook->getRiskScore(),
                $webhook->isLive()
            )
        );
        $this->transactionHistoryService->setTransactionHistory($transactionHistory);

        $newStateId = $this->orderStatusProvider->getOrderStatus($newState);
        if (!empty($newStateId)) {
            $this->orderService->updateOrderStatus($webhook, $newStateId);
        }
    }

    /**
     * @param TransactionHistory $transactionHistory
     * @param Webhook $webhook
     *
     * @return bool
     */
    protected function hasDuplicates(TransactionHistory $transactionHistory, Webhook $webhook): bool
    {
        $duplicatedItems = $transactionHistory->collection()->filterByPspReference(
            $webhook->getPspReference()
        )->filterByEventCode(
            $webhook->getEventCode()
        )->filterByStatus($webhook->isSuccess());

        return !$duplicatedItems->isEmpty() && $webhook->getOriginalReference(
            ) === $transactionHistory->getOriginalPspReference();
    }
}
