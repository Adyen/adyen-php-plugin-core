<?php

namespace Adyen\Core\Tests\BusinessLogic\Common\MockComponents;

use Adyen\Core\BusinessLogic\Domain\Integration\Order\OrderService;
use Adyen\Core\BusinessLogic\Domain\Webhook\Models\Webhook;

class MockOrderService implements OrderService
{

    /**
     * @inheritDoc
     */
    public function orderExists(string $merchantReference): bool
    {
        return true;
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
}
