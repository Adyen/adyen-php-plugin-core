<?php

namespace Adyen\Core\BusinessLogic\Domain\Webhook\Services;

use Adyen\Core\BusinessLogic\AdyenAPI\Management\Webhook\Http\Proxy;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Services\GeneralSettingsService;
use Adyen\Core\BusinessLogic\Domain\Integration\Order\OrderService;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Exceptions\InvalidMerchantReferenceException;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models\HistoryItem;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Services\TransactionHistoryService;
use Adyen\Core\BusinessLogic\Domain\Webhook\Models\Webhook;
use Adyen\Webhook\EventCodes;

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
     * @var GeneralSettingsService
     */
    protected $settingsService;

    /**
     * @param TransactionHistoryService $transactionHistoryService
     * @param OrderService $orderService
     * @param OrderStatusProvider $orderStatusProvider
     * @param GeneralSettingsService $settingsService
     */
    public function __construct(
        TransactionHistoryService $transactionHistoryService,
        OrderService              $orderService,
        OrderStatusProvider       $orderStatusProvider,
        GeneralSettingsService    $settingsService
    )
    {
        $this->transactionHistoryService = $transactionHistoryService;
        $this->orderService = $orderService;
        $this->orderStatusProvider = $orderStatusProvider;
        $this->settingsService = $settingsService;
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
            ) && $webhook->getMerchantReference() !== Proxy::TEST_WEBHOOK;
    }

    /**
     * @param Webhook $webhook
     *
     * @return void
     *
     * @throws InvalidMerchantReferenceException
     */
    public function synchronizeChanges(Webhook $webhook, bool $orderCreated = true): void
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

        if ($webhook->getEventCode() === EventCodes::AUTHORISATION) {
            return;
        }

        $settings = $this->settingsService->getGeneralSettings();

        if ($settings && $webhook->getEventCode() === EventCodes::CANCELLATION
            && !$settings->isCancelledPartialPayment()) {
            return;
        }

        $newStateId = $this->orderStatusProvider->getOrderStatus($newState);
        if (!empty($newStateId) && $orderCreated) {
            $this->orderService->updateOrderStatus($webhook, $newStateId);
        }

        if ($orderCreated && $webhook->isSuccess() &&
            $webhook->getEventCode() === EventCodes::ORDER_CLOSED &&
            $webhook->getPspReference() !== $transactionHistory->getOriginalPspReference()) {
            $this->orderService->updateOrderPayment($webhook);
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

        return !$duplicatedItems->isEmpty() && $webhook->getOriginalReference() === $transactionHistory->getOriginalPspReference();
    }
}
