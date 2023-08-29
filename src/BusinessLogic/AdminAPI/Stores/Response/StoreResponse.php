<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\Stores\Response;

use Adyen\Core\BusinessLogic\AdminAPI\Response\Response;
use Adyen\Core\BusinessLogic\Domain\Stores\Models\Store;

/**
 * Class StoreResponse
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\Stores\Response
 */
class StoreResponse extends Response
{
    /**
     * @var Store
     */
    private $store;

    /**
     * @param Store $store
     */
    public function __construct(Store $store)
    {
        $this->store = $store;
    }

    /**
     * Transforms store to array.
     *
     * @return array Array representation of store.
     */
    public function toArray(): array
    {
        return [
            'storeId' => $this->store->getStoreId(),
            'storeName' => $this->store->getStoreName(),
            'maintenanceMode' => $this->store->isMaintenanceMode()
        ];
    }
}
