<?php

namespace Adyen\Core\Tests\BusinessLogic\AdminAPI\OrderMappings\MockComponents;

use Adyen\Core\BusinessLogic\Domain\OrderSettings\Models\OrderStatusMapping;
use Adyen\Core\BusinessLogic\Webhook\Repositories\OrderStatusMappingRepository;

/**
 * Class MockOrderMappingRepository
 *
 * @package Adyen\Core\Tests\BusinessLogic\AdminAPI\OrderMappings\MockComponents
 */
class MockOrderMappingRepository implements OrderStatusMappingRepository
{
    /**
     * @var array
     */
    private $orderStatusMapping;

    public function __construct()
    {
        $this->orderStatusMapping = [];
    }

    /**
     * @inheritDoc
     */
    public function getOrderStatusMapping(): array
    {
        return $this->orderStatusMapping;
    }

    /**
     * @inheritDoc
     */
    public function setOrderStatusMapping(array $orderStatusMapping): void
    {
        $this->orderStatusMapping = $orderStatusMapping;
    }

    public function deleteOrderStatusMapping(): void
    {
    }
}
