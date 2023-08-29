<?php

namespace Adyen\Core\Tests\BusinessLogic\AdminAPI\Store;

use Adyen\Core\BusinessLogic\AdminAPI\AdminAPI;
use Adyen\Core\BusinessLogic\AdminAPI\Stores\Controller\StoreController;
use Adyen\Core\BusinessLogic\AdminAPI\Stores\Response\StoreOrderStatusResponse;
use Adyen\Core\BusinessLogic\AdminAPI\Stores\Response\StoreResponse;
use Adyen\Core\BusinessLogic\AdminAPI\Stores\Response\StoresResponse;
use Adyen\Core\BusinessLogic\Bootstrap\SingleInstance;
use Adyen\Core\BusinessLogic\DataAccess\Connection\Repositories\ConnectionSettingsRepository;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionSettings;
use Adyen\Core\BusinessLogic\Domain\Integration\Store\StoreService as IntegrationStoreService;
use Adyen\Core\BusinessLogic\Domain\Stores\Models\Store;
use Adyen\Core\BusinessLogic\Domain\Stores\Models\StoreOrderStatus;
use Adyen\Core\BusinessLogic\Domain\Stores\Services\StoreService;
use Adyen\Core\Tests\BusinessLogic\AdminAPI\Store\MockComponents\MockConnectionSettingsRepository;
use Adyen\Core\Tests\BusinessLogic\AdminAPI\Store\MockComponents\MockIntegrationStoreService;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class StoreApiTest
 *
 * @package Adyen\Core\Tests\BusinessLogic\AdminAPI\Store
 */
class StoreApiTest extends BaseTestCase
{
    /**
     * @var MockIntegrationStoreService
     */
    private $integrationStoreService;

    /**
     * @var MockConnectionSettingsRepository
     */
    private $connectionSettingsRepo;

    public function setUp(): void
    {
        parent::setUp();

        TestServiceRegister::registerService(
            ConnectionSettingsRepository::class,
            new SingleInstance(function () {
                return new MockConnectionSettingsRepository();
            })
        );
        $this->connectionSettingsRepo = TestServiceRegister::getService(ConnectionSettingsRepository::class);
        $this->integrationStoreService = new MockIntegrationStoreService();
        $this->storeService = new StoreService(
            $this->integrationStoreService,
            $this->connectionSettingsRepo
        );

        TestServiceRegister::registerService(
            IntegrationStoreService::class,
            new SingleInstance(function () {
                return $this->integrationStoreService;
            })
        );

        TestServiceRegister::registerService(
            StoreService::class,
            new SingleInstance(function () {
                return $this->storeService;
            })
        );

        TestServiceRegister::registerService(
            StoreController::class,
            new SingleInstance(function () {
                return new StoreController($this->storeService);
            })
        );
    }

    /**
     * @return void
     */
    public function testIsStoresResponseSuccessful(): void
    {
        // Arrange

        // Act
        $stores = AdminAPI::get()->store('1')->getStores();

        // Assert
        self::assertTrue($stores->isSuccessful());
    }

    /**
     * @return void
     */
    public function testIsStoreResponseSuccessful(): void
    {
        // Arrange

        // Act
        $currentStore = AdminAPI::get()->store('1')->getCurrentStore();

        // Assert
        self::assertTrue($currentStore->isSuccessful());
    }

    /**
     * @return void
     */
    public function testDefaultStoreResponse(): void
    {
        // Arrange
        $this->connectionSettingsRepo->setMockConnectionSettings(null);
        $this->integrationStoreService->setMockDefaultStore(new Store('store1', 'store12', true));

        // Act
        $currentStore = AdminAPI::get()->store('1')->getCurrentStore();

        // Assert
        self::assertEquals($currentStore, $this->expectedDefaultStoreResponse());
    }

    /**
     * @return void
     */
    public function testDefaultStoreResponseToArray(): void
    {
        // Arrange
        $this->connectionSettingsRepo->setMockConnectionSettings(null);
        $this->integrationStoreService->setMockDefaultStore(new Store('store1', 'store12', true));

        // Act
        $currentStore = AdminAPI::get()->store('1')->getCurrentStore();

        // Assert
        self::assertEquals($currentStore->toArray(), $this->expectedDefaultStoreResponse()->toArray());
    }

    /**
     * @return void
     */
    public function testCurrentStoreResponse(): void
    {
        // Arrange
        $this->connectionSettingsRepo->setMockConnectionSettings(
            new ConnectionSettings(
                '1',
                'test',
                new ConnectionData('1', '1'),
                null
            )
        );
        $this->integrationStoreService->setMockStoreByIdStore(new Store('1', 'store1', true));
        $this->integrationStoreService->setMockDefaultStore(new Store('2', '2', true));

        // Act
        $currentStore = AdminAPI::get()->store('1')->getCurrentStore();

        // Assert
        self::assertEquals($currentStore, $this->expectedStoreByIdResponse());
    }

    /**
     * @return void
     */
    public function testFailBackStoreResponse(): void
    {
        // Arrange
        $this->integrationStoreService->setMockStoreByIdStore(null);
        $this->integrationStoreService->setMockDefaultStore(null);

        // Act
        $currentStore = AdminAPI::get()->store('1')->getCurrentStore();

        // Assert
        self::assertEquals($currentStore, $this->expectedFailBackResponse());
    }

    /**
     * @return void
     */
    public function testStoresResponse(): void
    {
        // Arrange
        $this->integrationStoreService->setMockStores(
            [
                new Store('store1', 'store1', true),
                new Store('store2', 'store2', false),
                new Store('store3', 'store3', true)
            ]
        );

        // Act
        $stores = AdminAPI::get()->store('1')->getStores();

        // Assert
        self::assertEquals($stores, $this->expectedStoresResponse());
    }

    /**
     * @return void
     */
    public function testStoresResponseToArray(): void
    {
        // Arrange
        $this->integrationStoreService->setMockStores(
            [
                new Store('store1', 'store1', true),
                new Store('store2', 'store2', false),
                new Store('store3', 'store3', true)
            ]
        );

        // Act
        $stores = AdminAPI::get()->store('1')->getStores();

        // Assert
        self::assertEquals($stores->toArray(), $this->expectedStoresResponse()->toArray());
    }

    /**
     * @return void
     */
    public function testStoreOrderResponse(): void
    {
        // Arrange
        $this->integrationStoreService->setMockOrderStatuses(
            [
                new StoreOrderStatus('1', 'name1'),
                new StoreOrderStatus('2', 'name2'),
                new StoreOrderStatus('3', 'name3')
            ]
        );

        // Act
        $statuses = AdminAPI::get()->store('1')->getStoreOrderStatuses();

        // Assert
        self::assertEquals($statuses, $this->expectedOrderStatusesResponse());
    }

    /**
     * @return void
     */
    public function testStoreOrderResponseToArray(): void
    {
        // Arrange
        $this->integrationStoreService->setMockOrderStatuses(
            [
                new StoreOrderStatus('1', 'name1'),
                new StoreOrderStatus('2', 'name2'),
                new StoreOrderStatus('3', 'name3')
            ]
        );

        // Act
        $statuses = AdminAPI::get()->store('1')->getStoreOrderStatuses();

        // Assert
        self::assertEquals($statuses->toArray(), $this->expectedOrderStatusesResponse()->toArray());
    }

    /**
     * @return void
     */
    public function testStoreOrderResponseFail(): void
    {
        // Arrange
        $this->integrationStoreService->setMockOrderStatuses(
            [
                new StoreOrderStatus('1', 'name1'),
                new StoreOrderStatus('2', 'name2')
            ]
        );

        // Act
        $statuses = AdminAPI::get()->store('1')->getStoreOrderStatuses();

        // Assert
        self::assertNotEquals($statuses, $this->expectedOrderStatusesResponse());
    }

    /**
     * @return StoreOrderStatusResponse
     */
    private function expectedOrderStatusesResponse(): StoreOrderStatusResponse
    {
        return new StoreOrderStatusResponse([
            new StoreOrderStatus('1', 'name1'),
            new StoreOrderStatus('2', 'name2'),
            new StoreOrderStatus('3', 'name3')
        ]);
    }

    /**
     * @return StoresResponse
     */
    private function expectedStoresResponse(): StoresResponse
    {
        return new StoresResponse([
            new Store('store1', 'store1', true),
            new Store('store2', 'store2', false),
            new Store('store3', 'store3', true)
        ]);
    }

    /**
     * @return StoreResponse
     */
    private function expectedDefaultStoreResponse(): StoreResponse
    {
        return new StoreResponse(new Store('store1', 'store12', true));
    }

    /**
     * @return StoreResponse
     */
    private function expectedStoreByIdResponse(): StoreResponse
    {
        return new StoreResponse(new Store('1', 'store1', true));
    }

    /**
     * @return StoreResponse
     */
    private function expectedFailBackResponse(): StoreResponse
    {
        return new StoreResponse(new Store('failBack', 'failBack', false));
    }
}
