<?php

namespace Adyen\Core\BusinessLogic\Domain\Integration\Store;

use Adyen\Core\BusinessLogic\Domain\Stores\Models\Store;
use Adyen\Core\BusinessLogic\Domain\Stores\Models\StoreOrderStatus;

/**
 * Interface StoreService
 *
 * @package Adyen\Core\BusinessLogic\Domain\Integration\Store
 */
interface StoreService
{
    /**
     * Returns shop domain/url.
     *
     * @return string
     */
    public function getStoreDomain(): string;

    /**
     * Returns all stores within a multiple environment.
     *
     * @return Store[]
     */
    public function getStores(): array;

    /**
     * Returns current active store.
     *
     * @return Store|null
     */
    public function getDefaultStore(): ?Store;

    /**
     * Returns Store object based on id given as first parameter.
     *
     * @param string $id
     *
     * @return Store|null
     */
    public function getStoreById(string $id): ?Store;

    /**
     * Returns array of StoreOrderStatus objects.
     *
     * @return StoreOrderStatus[]
     */
    public function getStoreOrderStatuses(): array;

    /**
     * @return array
     */
    public function getDefaultOrderStatusMapping(): array;
}
