<?php

namespace Adyen\Core\BusinessLogic\Webhook\Tasks;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\InvalidCurrencyCode;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Currency;
use Adyen\Core\BusinessLogic\Domain\Integration\Order\OrderService;
use Adyen\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Authorization\FailedPaymentAuthorizationEvent;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Authorization\SuccessfulPaymentAuthorizationEvent;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\AuthorizationAdjustment\FailedAuthorizationAdjustmentEvent;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\AuthorizationAdjustment\SuccessfulAuthorizationAdjustmentEvent;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Cancellation\FailedCancellationEvent;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Cancellation\SuccessfulCancellationEvent;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Capture\FailedCaptureEvent;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Capture\SuccessfulCaptureEvent;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Chargebacks\SuccessfulChargebackEvent;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Event;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\PartialPayment\FailedOrderClosedEvent;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\PartialPayment\SuccessfulOrderClosedEvent;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Refund\FailedRefundEvent;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Refund\SuccessfulRefundEvent;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Services\ShopNotificationService;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Exceptions\InvalidMerchantReferenceException;
use Adyen\Core\BusinessLogic\Domain\Webhook\Models\Webhook;
use Adyen\Core\BusinessLogic\Domain\Webhook\Services\WebhookSynchronizationService;
use Adyen\Core\BusinessLogic\TransactionLog\Tasks\TransactionalTask;
use Adyen\Core\Infrastructure\Serializer\Interfaces\Serializable;
use Adyen\Core\Infrastructure\Serializer\Serializer;
use Adyen\Core\Infrastructure\ServiceRegister;
use Adyen\Webhook\EventCodes;
use Exception;

/**
 * Class OrderUpdateTask
 *
 * @package Adyen\Core\BusinessLogic\Domain\Webhook\Tasks
 */
class OrderUpdateTask extends TransactionalTask
{
    /**
     * @var Webhook
     */
    private $webhook;

    /**
     * @var string
     */
    private $storeId;

    /**
     * @var int
     */
    private $transactionLogId;

    /**
     * @param Webhook $webhook
     */
    public function __construct(Webhook $webhook)
    {
        $this->webhook = $webhook;
        $this->storeId = StoreContext::getInstance()->getStoreId();
    }

    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    public function execute(): void
    {
        StoreContext::doWithStore(
            $this->storeId,
            function () {
                $this->doExecute();
            }
        );
    }

    /**
     * Transforms array into a serializable object,
     *
     * @param array $array Data that is used to instantiate serializable object.
     *
     * @return Serializable  Instance of serialized object.
     *
     * @throws InvalidCurrencyCode
     * @throws Exception
     */
    public static function fromArray(array $array): Serializable
    {
        return StoreContext::doWithStore($array['storeId'], static function () use ($array) {
            return new static(
                new Webhook(
                    Amount::fromInt($array['amount']['value'], Currency::fromIsoCode($array['amount']['currency'])),
                    $array['eventCode'],
                    $array['eventDate'],
                    $array['hmacSignature'],
                    $array['merchantAccountCode'],
                    $array['merchantReference'],
                    $array['pspReference'],
                    $array['paymentMethod'],
                    $array['reason'],
                    $array['success'],
                    $array['originalReference'],
                    $array['riskScore'],
                    $array['live'],
                    $array['additionalData']
                )
            );
        });
    }

    /**
     * Transforms serializable object into an array.
     *
     * @return array Array representation of a serializable object.
     */
    public function toArray(): array
    {
        return array(
            'amount' => [
                'value' => $this->webhook->getAmount()->getValue(),
                'currency' => $this->webhook->getAmount()->getCurrency()->getIsoCode()
            ],
            'eventCode' => $this->webhook->getEventCode(),
            'eventDate' => $this->webhook->getEventDate(),
            'hmacSignature' => $this->webhook->getHmacSignature(),
            'merchantAccountCode' => $this->webhook->getMerchantAccountCode(),
            'merchantReference' => $this->webhook->getMerchantReference(),
            'pspReference' => $this->webhook->getPspReference(),
            'paymentMethod' => $this->webhook->getPaymentMethod(),
            'reason' => $this->webhook->getReason(),
            'success' => $this->webhook->isSuccess(),
            'originalReference' => $this->webhook->getOriginalReference(),
            'riskScore' => $this->webhook->getRiskScore(),
            'storeId' => $this->storeId,
            'live' => $this->webhook->isLive(),
            'additionalData' => $this->webhook->getAdditionalData(),
        );
    }

    /**
     * String representation of object
     *
     * @return string the string representation of the object or null
     */
    public function serialize(): string
    {
        return Serializer::serialize([$this->webhook, $this->storeId]);
    }

    /**
     * Constructs the object
     *
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     *
     * @return void
     */
    public function unserialize($serialized): void
    {
        [$this->webhook, $this->storeId] = Serializer::unserialize($serialized);
    }

    /**
     * @return Webhook
     */
    public function getWebhook(): Webhook
    {
        return $this->webhook;
    }

    /**
     * @return string
     */
    public function getStoreId(): string
    {
        return $this->storeId;
    }

    /**
     * @return int
     */
    public function getTransactionLogId(): ?int
    {
        return $this->transactionLogId;
    }

    /**
     * @return void
     */
    public function setTransactionLogId(?int $transactionLogId): void
    {
        $this->transactionLogId = $transactionLogId;
    }

    /**
     * @return void
     *
     * @throws InvalidMerchantReferenceException
     * @throws Exception
     */
    private function doExecute(): void
    {
        if ($this->checkIfCartExists()) {
            $event = $this->getEventFromWebhook();

            if ($event) {
                $this->getShopNotificationService()->pushNotification($event);
            }

            $this->getSynchronizationService()->synchronizeChanges($this->webhook, $this->checkIfOrderExists(), $this->getTransactionLogId());
        }

        $this->reportProgress(100);
    }

    /**
     * Returns instance of Event if event code is AUTHORISATION, CANCELLATION, CAPTURE or REFUND.
     *
     * @return ?Event
     */
    private function getEventFromWebhook(): ?Event
    {
        $event = $this->webhook->getEventCode();
        $success = $this->webhook->isSuccess();
        $merchantReference = $this->webhook->getMerchantReference();
        $paymentMethod = $this->webhook->getPaymentMethod();

        if ($event === EventCodes::AUTHORISATION && $success) {
            return new SuccessfulPaymentAuthorizationEvent($merchantReference, $paymentMethod);
        }

        if ($event === EventCodes::AUTHORISATION && !$success) {
            return new FailedPaymentAuthorizationEvent($merchantReference, $paymentMethod);
        }

        if ($event === EventCodes::CANCELLATION && $success) {
            return new SuccessfulCancellationEvent($merchantReference, $paymentMethod);
        }

        if ($event === EventCodes::CANCELLATION && !$success) {
            return new FailedCancellationEvent($merchantReference, $paymentMethod);
        }

        if ($event === EventCodes::CAPTURE && $success) {
            return new SuccessfulCaptureEvent($merchantReference, $paymentMethod);
        }

        if ($event === EventCodes::CAPTURE && !$success) {
            return new FailedCaptureEvent($merchantReference, $paymentMethod);
        }

        if ($event === EventCodes::REFUND && $success) {
            return new SuccessfulRefundEvent($merchantReference, $paymentMethod);
        }

        if ($event === EventCodes::REFUND && !$success) {
            return new FailedRefundEvent($merchantReference, $paymentMethod);
        }

        if ($event === EventCodes::AUTHORISATION_ADJUSTMENT && $success) {
            return new SuccessfulAuthorizationAdjustmentEvent($merchantReference, $paymentMethod);
        }

        if ($event === EventCodes::AUTHORISATION_ADJUSTMENT && !$success) {
            return new FailedAuthorizationAdjustmentEvent($merchantReference, $paymentMethod);
        }

        if ($event === EventCodes::CHARGEBACK && $success) {
            return new SuccessfulChargebackEvent($merchantReference, $paymentMethod);
        }

        if ($event === EventCodes::ORDER_CLOSED && $success) {
            return new SuccessfulOrderClosedEvent($merchantReference, $paymentMethod);
        }

        if ($event === EventCodes::ORDER_CLOSED && !$success) {
            return new FailedOrderClosedEvent($merchantReference, $paymentMethod);
        }

        return null;
    }

    /**
     * Returns true if cart is created in shop system.
     *
     * @return bool
     */
    private function checkIfCartExists(): bool
    {
        return $this->getOrderService()->cartExists($this->webhook->getMerchantReference());
    }

    /**
     * Returns true if order is created in shop system. If it is not created sleep for 2 seconds and check again.
     * Repeat this checking for 5 times.
     *
     * @param int $retryCount
     *
     * @return bool
     *
     * @throws Exception
     */
    private function checkIfOrderExists(int $retryCount = 0): bool
    {
        $order = false;

        try {
            $order = $this->getOrderService()->orderExists($this->webhook->getMerchantReference());
            while (!$order && $retryCount < 5) {
                sleep(2);
                $this->reportAlive();

                $order = $this->getOrderService()->orderExists($this->webhook->getMerchantReference());
                $retryCount++;
            }
        } catch (Exception $exception) {
            $retryCount++;
            if ($retryCount < 5) {
                sleep(2);
                $this->reportAlive();

                return $this->checkIfOrderExists($retryCount);
            }
        }

        return $order;
    }

    /**
     * @return ShopNotificationService
     */
    private function getShopNotificationService(): ShopNotificationService
    {
        return ServiceRegister::getService(ShopNotificationService::class);
    }

    /**
     * @return OrderService
     */
    private function getOrderService(): OrderService
    {
        return ServiceRegister::getService(OrderService::class);
    }

    /**
     * @return WebhookSynchronizationService
     */
    private function getSynchronizationService(): WebhookSynchronizationService
    {
        return ServiceRegister::getService(WebhookSynchronizationService::class);
    }
}
