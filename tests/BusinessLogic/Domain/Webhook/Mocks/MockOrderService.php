<?php

namespace Adyen\Core\Tests\BusinessLogic\Domain\Webhook\Mocks;

use Adyen\Core\BusinessLogic\Domain\Integration\Order\OrderService;
use Adyen\Core\BusinessLogic\Domain\Webhook\Models\Webhook;

/**
 * Class MockOrderService
 *
 * @package Adyen\Core\Tests\BusinessLogic\Webhook\Mocks
 */
class MockOrderService implements OrderService
{

    /**
     * @var bool
     */
    private $order;

    public function __construct()
    {
        $this->order = true;
    }

    /**
     * @inheritDoc
     */
    public function orderExists(string $merchantReference): bool
    {
        return $this->order;
    }

    /**
     * @param Webhook $webhook
     * @param string $statusID
     * @return void
     */
    public function updateOrderStatus(Webhook $webhook, string $statusID): void
    {
    }

    /**
     * @param bool $exists
     *
     * @return void
     */
    public function setMockOrderExists(bool $exists): void
    {
        $this->order = $exists;
    }

    public function getOrderCurrency(string $merchantReference): string
    {
        return 'EUR';
    }

    public function getOrderUrl(string $merchantReference): string
    {
        return '';
    }
}
