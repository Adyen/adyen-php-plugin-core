<?php

namespace Adyen\Core\BusinessLogic\Domain\Integration\Order;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\Webhook\Models\Webhook;

/**
 * Interface OrderService
 *
 * @package Adyen\Core\BusinessLogic\Domain\Integration\Order
 */
interface OrderService
{
    /**
     * @param string $merchantReference
     *
     * @return bool
     */
    public function orderExists(string $merchantReference): bool;

    /**
     * @param Webhook $webhook
     * @param string $statusId
     *
     * @return void
     */
    public function updateOrderStatus(Webhook $webhook, string $statusId): void;

    /**
     * @param string $merchantReference
     *
     * @return string
     */
    public function getOrderCurrency(string $merchantReference): string;

    /**
     * @param string $merchantReference
     *
     * @return string
     */
    public function getOrderUrl(string $merchantReference): string;

    /**
     * @param Webhook $webhook
     *
     * @return void
     */
    public function updateOrderPayment(Webhook $webhook): void;

    /**
     * @param string $merchantReference
     *
     * @return Amount
     */
    public function getOrderAmount(string $merchantReference): Amount;

    /**
     * Creates the order for a successful AUTHORISATION webhook when the shopper never returned to the shop to
     * create it. Called only for methods in PaymentMethodCode::WEBHOOK_ORDER_CREATION_SUPPORTED, and only after
     * orderExists() kept failing.
     *
     * @param Webhook $webhook
     *
     * @return bool True when the order exists afterwards, false when it could not be created.
     */
    public function createOrderFromWebhook(Webhook $webhook): bool;
}
