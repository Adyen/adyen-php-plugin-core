<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\Stores\Controller;

use Adyen\Core\BusinessLogic\AdminAPI\Stores\Response\StoreOrderStatusResponse;
use Adyen\Core\BusinessLogic\AdminAPI\Stores\Response\StoreResponse;
use Adyen\Core\BusinessLogic\AdminAPI\Stores\Response\StoresResponse;
use Adyen\Core\BusinessLogic\Domain\Stores\Exceptions\FailedToRetrieveOrderStatusesException;
use Adyen\Core\BusinessLogic\Domain\Stores\Exceptions\FailedToRetrieveStoresException;
use Adyen\Core\BusinessLogic\Domain\Stores\Models\Store;
use Adyen\Core\BusinessLogic\Domain\Stores\Services\StoreService;

/**
 * Class StoreController
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\Stores\Controller
 */
class StoreController
{
    /**
     * @var StoreService
     */
    private $storeService;

    /**
     * @param StoreService $storeService
     */
    public function __construct(StoreService $storeService)
    {
        $this->storeService = $storeService;
    }

    /**
     * @return StoresResponse
     *
     * @throws FailedToRetrieveStoresException
     */
    public function getStores(): StoresResponse
    {
        return new StoresResponse($this->storeService->getStores());
    }

    /**
     * @return StoreResponse
     *
     * @throws FailedToRetrieveStoresException
     */
    public function getCurrentStore(): StoreResponse
    {
        $currentStore = $this->storeService->getCurrentStore();

        return $currentStore ? new StoreResponse($currentStore) : new StoreResponse($this->failBackStore());
    }

    /**
     * @return StoreOrderStatusResponse
     *
     * @throws FailedToRetrieveOrderStatusesException
     */
    public function getStoreOrderStatuses(): StoreOrderStatusResponse
    {
        return new StoreOrderStatusResponse($this->storeService->getStoreOrderStatuses());
    }

    /**
     * Creates failBack store in case there is no connected and default store.
     *
     * @return Store
     */
    private function failBackStore(): Store
    {
        return new Store('failBack', 'failBack', false);
    }
}
