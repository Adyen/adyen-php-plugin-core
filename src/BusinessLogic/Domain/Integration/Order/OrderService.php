<?php

namespace Adyen\Core\BusinessLogic\Domain\Integration\Order;

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
}
