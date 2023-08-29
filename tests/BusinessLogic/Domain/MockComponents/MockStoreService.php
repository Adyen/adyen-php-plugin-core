<?php

namespace Adyen\Core\Tests\BusinessLogic\Domain\MockComponents;

use Adyen\Core\BusinessLogic\Domain\Integration\Store\StoreService;
use Adyen\Core\BusinessLogic\Domain\OrderSettings\Models\OrderStatusMapping;
use Adyen\Core\BusinessLogic\Domain\Stores\Models\Store;

/**
 * Class MockIntegrationStoreService
 *
 * @package Adyen\Core\Tests\BusinessLogic\Domain\MockComponents
 */
class MockStoreService implements StoreService
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
    public function getStoreDomain(): string
    {
        return 'unit-test.com';
    }

    public function getStores(): array
    {
        return [];
    }

    public function getDefaultStore(): Store
    {
        return new Store('1', '2', true);
    }

    public function getStoreById(string $id): ?Store
    {
        return new Store('1', '2', true);
    }

    public function getStoreOrderStatuses(): array
    {
        return [];
    }

    public function getDefaultOrderStatusMapping(): array
    {
        return $this->orderStatusMapping;
    }

    /**
     * @param array $mapping
     *
     * @return void
     */
    public function setMockOrderStatusMapping(array $mapping): void
    {
        $this->orderStatusMapping = $mapping;
    }
}
