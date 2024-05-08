<?php

namespace Adyen\Core\Tests\BusinessLogic\WebhookAPI\MockComponents;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Currency;
use Adyen\Core\BusinessLogic\Domain\Integration\Order\OrderService;
use Adyen\Core\BusinessLogic\Domain\Webhook\Models\Webhook;

/**
 * Class MockOrderService
 *
 * @package Adyen\Core\Tests\BusinessLogic\WebhookAPI\MockComponents
 */
class MockOrderService implements OrderService
{

    /**
     * @inheritDoc
     */
    public function orderExists(string $merchantReference): bool
    {
        return false;
    }

    public function updateOrderStatus(Webhook $webhook, string $statusId): void
    {
    }

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
}
