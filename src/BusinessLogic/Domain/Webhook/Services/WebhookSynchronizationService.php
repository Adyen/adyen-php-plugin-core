<?php

namespace Adyen\Core\BusinessLogic\Domain\Webhook\Services;

use Adyen\Core\BusinessLogic\AdyenAPI\Management\Webhook\Http\Proxy;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PaymentMethodCode;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Models\CaptureType;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Models\GeneralSettings;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Services\GeneralSettingsService;
use Adyen\Core\BusinessLogic\Domain\Integration\Order\OrderService;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\ShopEvents;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Exceptions\InvalidMerchantReferenceException;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models\HistoryItem;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models\HistoryItemCollection;
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

        if ($webhook->getEventCode() === EventCodes::OFFER_CLOSED &&
            !$transactionHistory->collection()->filterAllByEventCode(EventCodes::AUTHORISATION)
                ->filterAllByStatus(true)->isEmpty()) {
            return;
        }

        if ($webhook->getEventCode() === EventCodes::ORDER_CLOSED && !$webhook->isSuccess()) {
            $this->handleOrderClosedFailure($webhook, $transactionHistory);

            if (!$transactionHistory->collection()->filterAllByEventCode(EventCodes::AUTHORISATION)
                ->filterAllByStatus(true)->isEmpty()) {
                return;
            }
        }

        $newState = $this->orderStatusProvider->getNewPaymentState($webhook, $transactionHistory);
        $settings = $this->settingsService->getGeneralSettings();

        if ($this->shouldDisableOrderModifications($settings, $webhook)) {
            $lastTransactionHistoryItem = $transactionHistory->collection()
                ->filterByOriginalReference($webhook->getOriginalReference())->last();
            $previousPaymentState = $lastTransactionHistoryItem ? $lastTransactionHistoryItem->getPaymentState() : '';
            $newState = $previousPaymentState;
        }

        $captureType = $transactionHistory->getCaptureType();
        if (
            in_array($webhook->getPaymentMethod(), PaymentMethodCode::SUPPORTED_PAYMENT_METHODS, true) &&
            !PaymentMethodCode::parse($webhook->getPaymentMethod())->isCaptureSupported()
        ) {
            $captureType = CaptureType::immediate();
        }

        $isPaymentLinkSet = $transactionHistory->getPaymentLink();
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
                $webhook->isLive(),
                $webhook->getEventCode() === EventCodes::AUTHORISATION ? $webhook->getPspReference() : $webhook->getOriginalReference(),
                $captureType
            )
        );

        $references = $transactionHistory->getAuthorizationPspReferences();
        if ($webhook->getEventCode() === EventCodes::CANCELLATION) {
            $transactionHistory->setAuthorizationPspReferences(array_diff($references, [$webhook->getOriginalReference()]));
        }

        if ($webhook->getEventCode() === EventCodes::AUTHORISATION && $webhook->isSuccess()) {
            $transactionHistory->setAuthorizationPspReferences(array_unique(array_merge($references, [$webhook->getPspReference()])));
        }

        if ($webhook->getEventCode() === EventCodes::ORDER_CLOSED && $webhook->isSuccess()) {
            $this->handleOrderClosedSuccess($webhook, $transactionHistory);
        }

        $this->transactionHistoryService->setTransactionHistory($transactionHistory);

        if ($this->shouldNotHandleWebhook($webhook, $settings, $transactionHistory)) {
            return;
        }

        if ($this->shouldDisableOrderModifications($settings, $webhook)) {
            return;
        }

        $newStateId = $this->orderStatusProvider->getOrderStatus($newState);
        if (!empty($newStateId)) {
            $this->orderService->updateOrderStatus($webhook, $newStateId);
        }

        if ($webhook->isSuccess() &&
            in_array($webhook->getEventCode(), [EventCodes::ORDER_CLOSED, EventCodes::AUTHORISATION], true) &&
            (
                $webhook->getPspReference() !== $transactionHistory->getOriginalPspReference() ||
                $isPaymentLinkSet
            )
        ) {
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

    /**
     * @param Webhook $webhook
     * @param GeneralSettings|null $settings
     * @param TransactionHistory $transactionHistory
     * @return bool
     */
    protected function shouldNotHandleWebhook(Webhook $webhook, ?GeneralSettings $settings, TransactionHistory $transactionHistory): bool
    {
        $paymentLinkTransactionsExists = !$transactionHistory->collection()
            ->filterAllByEventCode(ShopEvents::PAYMENT_LINK_CREATED)
            ->isEmpty();
        if ($paymentLinkTransactionsExists && $webhook->getEventCode() === EventCodes::AUTHORISATION) {
            return false;
        }

        return in_array($webhook->getEventCode(), [EventCodes::AUTHORISATION, EventCodes::ORDER_OPENED], true) ||
            ($settings && $webhook->getEventCode() === EventCodes::CANCELLATION
                && !$settings->isCancelledPartialPayment() &&
                count($transactionHistory->getAuthorizationPspReferences()) > 0);
    }

    /**
     * @param Webhook $webhook
     * @param TransactionHistory $transactionHistory
     *
     * @return void
     */
    protected function handleOrderClosedFailure(Webhook $webhook, TransactionHistory $transactionHistory): void
    {
        $eventPSPReference = $webhook->getPspReference();
        $references = $webhook->getPspReferencesFromAdditionalData();

        $obsoleteItems = [$transactionHistory->collection()->filterAllByPspReference($eventPSPReference)->getAll()];
        foreach ($references as $reference) {
            $obsoleteItems[] = $transactionHistory->collection()->filterAllByPspReference($reference)->getAll();
            $obsoleteItems[] = $transactionHistory->collection()->filterByOriginalReference($reference)->getAll();
        }

        $obsoleteItems = array_merge(...$obsoleteItems);
        $allItems = $transactionHistory->collection()->getAll();
        $items = [];

        foreach ($allItems as $item) {
            if (!in_array($item, $obsoleteItems, true)) {
                $items[] = $item;
            }
        }

        $transactionHistory->setAuthorizationPspReferences(array_diff($transactionHistory->getAuthorizationPspReferences(), $references));
        $transactionHistory->setCollection(new HistoryItemCollection($items));
        $this->transactionHistoryService->setTransactionHistory($transactionHistory);
    }

    protected function handleOrderClosedSuccess(Webhook $webhook, TransactionHistory $transactionHistory): void
    {
        $references = $webhook->getPspReferencesFromAdditionalData();

        $obsoleteItems = [];
        foreach ($transactionHistory->getAuthorizationPspReferences() as $reference) {
            if (!in_array($reference, $references, true)) {
                $obsoleteItems[] = $transactionHistory->collection()->filterAllByPspReference($reference)->getAll();
            }
        }

        $obsoleteItems = array_merge(...$obsoleteItems);
        $allItems = $transactionHistory->collection()->getAll();
        $items = [];

        foreach ($allItems as $item) {
            if (!in_array($item, $obsoleteItems, true)) {
                $items[] = $item;
            }
        }

        $transactionHistory->setAuthorizationPspReferences($references);
        $transactionHistory->setCollection(new HistoryItemCollection($items));
        $this->transactionHistoryService->setTransactionHistory($transactionHistory);
    }

    /**
     * @param GeneralSettings|null $generalSettings
     * @param Webhook $webhook
     * @return bool
     */
    private function shouldDisableOrderModifications(?GeneralSettings $generalSettings, Webhook $webhook): bool
    {
        return $generalSettings &&
            $generalSettings->areDisabledOrderModificationsForFailedRefund() &&
            $webhook->getEventCode() === EventCodes::REFUND &&
            !$webhook->isSuccess();
    }
}
