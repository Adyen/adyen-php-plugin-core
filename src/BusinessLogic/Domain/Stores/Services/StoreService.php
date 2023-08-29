<?php

namespace Adyen\Core\BusinessLogic\Domain\Stores\Services;

use Adyen\Core\BusinessLogic\Domain\Connection\Repositories\ConnectionSettingsRepository;
use Adyen\Core\BusinessLogic\Domain\Integration\Store\StoreService as IntegrationStoreService;
use Adyen\Core\BusinessLogic\Domain\Stores\Exceptions\FailedToRetrieveOrderStatusesException;
use Adyen\Core\BusinessLogic\Domain\Stores\Exceptions\FailedToRetrieveStoresException;
use Adyen\Core\BusinessLogic\Domain\Stores\Models\Store;
use Adyen\Core\BusinessLogic\Domain\Stores\Models\StoreOrderStatus;
use Exception;

/**
 * Class StoreService
 *
 * @package Adyen\Core\BusinessLogic\Domain\Stores\Services
 */
class StoreService
{
    /**
     * @var ConnectionSettingsRepository
     */
    protected $connectionSettingsRepository;

    /**
     * @var IntegrationStoreService
     */
    protected $integrationStoreService;

    public function __construct(
        IntegrationStoreService      $integrationStoreService,
        ConnectionSettingsRepository $connectionSettingsRepository
    )
    {
        $this->integrationStoreService = $integrationStoreService;
        $this->connectionSettingsRepository = $connectionSettingsRepository;
    }

    /**
     * @return Store[]
     *
     * @throws FailedToRetrieveStoresException
     */
    public function getStores(): array
    {
        try {
            return $this->integrationStoreService->getStores();
        } catch (Exception $e) {
            throw new FailedToRetrieveStoresException($e);
        }
    }

    /**
     * Returns first connected store. If it does not exist, default store is returned.
     *
     * @return Store|null
     * @throws FailedToRetrieveStoresException
     */
    public function getCurrentStore(): ?Store
    {
        try {
            $firstConnectedStoreId = $this->getFirstConnectedStoreId();

            return $firstConnectedStoreId ? $this->integrationStoreService->getStoreById(
                $firstConnectedStoreId
            ) : $this->integrationStoreService->getDefaultStore();
        } catch (Exception $e) {
            throw new FailedToRetrieveStoresException($e);
        }
    }

    /**
     * Returns array of StoreOrderStatus objects.
     *
     * @return StoreOrderStatus[]
     *
     * @throws FailedToRetrieveOrderStatusesException
     */
    public function getStoreOrderStatuses(): array
    {
        try {
            return $this->integrationStoreService->getStoreOrderStatuses();
        } catch (Exception $e) {
            throw new FailedToRetrieveOrderStatusesException($e);
        }
    }

    /**
     * Returns ID of first store that was connected to Adyen. If there is no store connected, empty string is returned.
     *
     * @return string
     */
    public function getFirstConnectedStoreId(): string
    {
        $connectionSettings = $this->connectionSettingsRepository->getOldestConnectionSettings();

        return $connectionSettings ? $connectionSettings->getStoreId() : '';
    }
}
