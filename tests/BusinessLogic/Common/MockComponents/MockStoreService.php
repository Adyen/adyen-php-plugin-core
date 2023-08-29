<?php

namespace Adyen\Core\Tests\BusinessLogic\Common\MockComponents;

use Adyen\Core\BusinessLogic\Domain\OrderSettings\Models\OrderStatusMapping;
use Adyen\Core\BusinessLogic\Domain\Stores\Models\Store;

class MockStoreService implements \Adyen\Core\BusinessLogic\Domain\Integration\Store\StoreService
{
    /**
     * @var array
     */
    private $defaultMap;

    public function __construct()
    {
        $this->defaultMap = [];
    }

    /**
     * @inheritDoc
     */
    public function getStoreDomain(): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getStores(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getDefaultStore(): Store
    {
        return new Store('1', 'Test', false);
    }

    /**
     * @inheritDoc
     */
    public function getStoreById(string $id): ?Store
    {
        return new Store('1', 'Test', false);
    }

    /**
     * @return array|\Adyen\Core\BusinessLogic\Domain\Stores\Models\StoreOrderStatus[]
     */
    public function getStoreOrderStatuses(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function getDefaultOrderStatusMapping(): array
    {
        return $this->defaultMap;
    }

    /**
     * @param array $map
     * @return void
     */
    public function setMockDefaultMap(array $map): void
    {
        $this->defaultMap = $map;
    }
}
