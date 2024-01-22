<?php

namespace Adyen\Core\BusinessLogic\Webhook\Services;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\CurrencyMismatchException;
use Adyen\Core\BusinessLogic\Domain\Integration\Store\StoreService;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory;
use Adyen\Core\BusinessLogic\Domain\Webhook\Models\Webhook;
use Adyen\Core\BusinessLogic\Domain\Webhook\Services\OrderStatusProvider;
use Adyen\Core\BusinessLogic\Webhook\Repositories\OrderStatusMappingRepository;
use Adyen\Webhook\EventCodes;
use Adyen\Webhook\Exception\InvalidDataException;
use Adyen\Webhook\Notification;
use Adyen\Webhook\PaymentStates;
use Adyen\Webhook\Processor\ProcessorFactory;

/**
 * Class OrderStatusMappingService
 *
 * @package Adyen\Core\BusinessLogic\Domain\OrderStatusMapping\Services
 */
class OrderStatusMappingService implements OrderStatusProvider
{
    /**
     * @var OrderStatusMappingRepository
     */
    private $repository;

    /**
     * @var StoreService
     */
    private $storeService;

    /**
     * @param OrderStatusMappingRepository $repository
     * @param StoreService $storeService
     */
    public function __construct(OrderStatusMappingRepository $repository, StoreService $storeService)
    {
        $this->repository = $repository;
        $this->storeService = $storeService;
    }

    /**
     * @param array $orderStatusMappingSettings
     *
     * @return void
     */
    public function saveOrderStatusMappingSettings(array $orderStatusMappingSettings): void
    {
        $this->repository->setOrderStatusMapping($orderStatusMappingSettings);
    }

    /**
     * @return array
     */
    public function getOrderStatusMappingSettings(): array
    {
        $orderStatusMapping = $this->repository->getOrderStatusMapping();

        return !empty($orderStatusMapping) ? $orderStatusMapping : $this->getDefaultStatusMapping();
    }

    /**
     * @return array
     */
    private function getDefaultStatusMapping(): array
    {
        return array_merge([
            PaymentStates::STATE_IN_PROGRESS => '',
            PaymentStates::STATE_PENDING => '',
            PaymentStates::STATE_PAID => '',
            PaymentStates::STATE_FAILED => '',
            PaymentStates::STATE_REFUNDED => '',
            PaymentStates::STATE_CANCELLED => '',
            PaymentStates::STATE_PARTIALLY_REFUNDED => '',
            PaymentStates::STATE_NEW => '',
            PaymentStates::CHARGE_BACK => ''
        ], $this->storeService->getDefaultOrderStatusMapping());
    }

    /**
     * @param string $state
     *
     * @return string
     */
    public function getOrderStatus(string $state): string
    {
        $mapping = $this->getOrderStatusMappingSettings();

        return $state ? $mapping[$state] : '';
    }

    /**
     * @param Webhook $webhook
     * @param TransactionHistory $transactionHistory
     * @return string|null
     *
     * @throws InvalidDataException
     * @throws CurrencyMismatchException
     */
    public function getNewPaymentState(Webhook $webhook, TransactionHistory $transactionHistory): ?string
    {
        $lastTransactionHistoryItem = $transactionHistory->collection()->last();
        $previousPaymentState = $lastTransactionHistoryItem ? $lastTransactionHistoryItem->getPaymentState() : '';
        $capturedAmount = $transactionHistory->getCapturedAmount();
        $refundedAmount = $transactionHistory->getTotalAmountForEventCode(EventCodes::REFUND);

        if (empty($previousPaymentState)) {
            $previousPaymentState = PaymentStates::STATE_NEW;
        }

        $notificationItem = Notification::createItem([
            'eventCode' => $webhook->getEventCode(),
            'success' => $webhook->isSuccess()
        ]);

        $processor = ProcessorFactory::create(
            $notificationItem,
            $previousPaymentState
        );

        $newState = $processor->process();

        if ($webhook->isSuccess() && $webhook->getEventCode(
            ) === EventCodes::CANCELLATION && !$capturedAmount) {
            $newState = PaymentStates::STATE_CANCELLED;
        }

        if ($webhook->isSuccess() && $webhook->getEventCode() === EventCodes::REFUND &&
            $refundedAmount->plus($webhook->getAmount())->getValue() < $capturedAmount->getValue()) {
            $newState = PaymentStates::STATE_PARTIALLY_REFUNDED;
        }

        if ($webhook->isSuccess() && $webhook->getEventCode(
            ) === EventCodes::CAPTURE && $previousPaymentState === PaymentStates::STATE_REFUNDED) {
            $newState = PaymentStates::STATE_PARTIALLY_REFUNDED;
        }

        if ($webhook->isSuccess() &&
            $webhook->getEventCode() === EventCodes::AUTHORISATION &&
            $webhook->getPspReference() !== $transactionHistory->getOriginalPspReference() &&
            ($previousPaymentState === PaymentStates::STATE_FAILED || $previousPaymentState === PaymentStates::STATE_CANCELLED)) {
            $newState = PaymentStates::STATE_PAID;;
        }

        if (!$webhook->isSuccess() &&
            $webhook->getEventCode() === EventCodes::AUTHORISATION &&
            $webhook->getPspReference() !== $transactionHistory->getOriginalPspReference()) {
            $newState = PaymentStates::STATE_FAILED;;
        }

        return $newState;
    }
}
