<?php

namespace Adyen\Core\Tests\BusinessLogic\Common\MockComponents;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Currency;
use Adyen\Core\BusinessLogic\Domain\Integration\Order\OrderService;
use Adyen\Core\BusinessLogic\Domain\Webhook\Models\Webhook;

class MockOrderService implements OrderService
{
    public static $orderExists = true;

    /**
     * @inheritDoc
     */
    public function orderExists(string $merchantReference): bool
    {
        return static::$orderExists;
    }

    /**
     * @inheritDoc
     */
    public function updateOrderStatus(Webhook $webhook, string $statusId): void
    {
    }

    /**
     * @inheritDoc
     */
    public function getOrderCurrency(string $merchantReference): string
    {
        return 'EUR';
    }

    public function getOrderUrl(string $merchantReference): string
    {
        return '';
    }
    public function updateOrderPayment(Webhook $webhook): void
    {
    }

    public function getOrderAmount(string $merchantReference): Amount
    {
        return Amount::fromInt(1, Currency::getDefault());
    }

    public function cartExists(string $merchantReference): bool
    {
        return true;
    }
}
