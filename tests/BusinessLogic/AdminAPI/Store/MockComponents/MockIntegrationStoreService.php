<?php

namespace Adyen\Core\Tests\BusinessLogic\AdminAPI\Store\MockComponents;

use Adyen\Core\BusinessLogic\Domain\Integration\Store\StoreService;
use Adyen\Core\BusinessLogic\Domain\OrderSettings\Models\OrderStatusMapping;
use Adyen\Core\BusinessLogic\Domain\Stores\Models\Store;
use Adyen\Core\BusinessLogic\Domain\Stores\Models\StoreOrderStatus;

class MockIntegrationStoreService implements StoreService
{
    /**
     * @var Store
     */
    private $defaultStore;

    /**
     * @var Store
     */
    private $storeById;

    /**
     * @var Store[]
     */
    private $stores;

    /**
     * @var StoreOrderStatus[]
     */
    private $orderStatuses;

    public function __construct()
    {
        $this->orderStatuses = [];
        $this->stores = [];
        $this->defaultStore = new Store('1', 'name1', true);
        $this->storeById = new Store('1', 'name1', true);
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
        return $this->stores;
    }

    /**
     * @inheritDoc
     */
    public function getDefaultStore(): Store
    {
        return $this->defaultStore;
    }

    /**
     * @param Store|null $store
     *
     * @return void
     */
    public function setMockDefaultStore(?Store $store): void
    {
        $this->defaultStore = $store;
    }

    /**
     * @param Store|null $store
     *
     * @return void
     */
    public function setMockStoreByIdStore(?Store $store): void
    {
        $this->storeById = $store;
    }

    /**
     * @param Store[] $stores
     *
     * @return void
     */
    public function setMockStores(array $stores): void
    {
        $this->stores = $stores;
    }

    /**
     * @param StoreOrderStatus[] $statuses
     *
     * @return void
     */
    public function setMockOrderStatuses(array $statuses): void
    {
        $this->orderStatuses = $statuses;
    }

    /**
     * @param string $id
     *
     * @return Store|null
     */
    public function getStoreById(string $id): ?Store
    {
        return $this->storeById;
    }

    public function getStoreOrderStatuses(): array
    {
        return $this->orderStatuses;
    }

    public function getDefaultOrderStatusMapping(): array
    {
        return [];
    }
}
