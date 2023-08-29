<?php

namespace Adyen\Core\Tests\BusinessLogic\DataAccess\OrderSettings\Repositories;

use Adyen\Core\BusinessLogic\DataAccess\OrderSettings\Entities\OrderStatusMapping as OrderStatusMappingSettingsEntity;
use Adyen\Core\BusinessLogic\Domain\Integration\Store\StoreService;
use Adyen\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Adyen\Core\BusinessLogic\Webhook\Services\OrderStatusMappingService;
use Adyen\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use Adyen\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Adyen\Core\Tests\BusinessLogic\Common\MockComponents\MockStoreService;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;
use Adyen\Webhook\PaymentStates;
use Exception;

/**
 * Class OrderStatusMappingRepositoryTest
 *
 * @package Adyen\Core\Tests\BusinessLogic\DataAccess\OrderSettings\Repositories
 */
class OrderStatusMappingRepositoryTest extends BaseTestCase
{
    /**
     * @var RepositoryInterface
     */
    private $repository;

    /**
     * @var OrderStatusMappingService
     */
    private $service;

    /**
     * @var StoreService
     */
    private $storeService;

    /**
     * @throws RepositoryNotRegisteredException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->storeService = new MockStoreService();
        TestServiceRegister::registerService(StoreService::class, function () {
            return $this->storeService;
        });
        $this->repository = TestRepositoryRegistry::getRepository(OrderStatusMappingSettingsEntity::getClassName());
        $this->service = TestServiceRegister::getService(OrderStatusMappingService::class);
    }

    /**
     * @throws Exception
     */
    public function testGetSettingsNoSettings(): void
    {
        // act
        $result = StoreContext::doWithStore('1', [$this->service, 'getOrderStatusMappingSettings']);

        // assert
        self::assertEquals([
            PaymentStates::STATE_IN_PROGRESS => '',
            PaymentStates::STATE_PENDING => '',
            PaymentStates::STATE_PAID => '',
            PaymentStates::STATE_FAILED => '',
            PaymentStates::STATE_REFUNDED => '',
            PaymentStates::STATE_CANCELLED => '',
            PaymentStates::STATE_PARTIALLY_REFUNDED => '',
            PaymentStates::STATE_NEW => '',
            PaymentStates::CHARGE_BACK => ''
        ], $result);
    }

    /**
     * @throws Exception
     */
    public function testGetSettingsWithDefault(): void
    {
        // act
        /** @var MockStoreService $storeService */
        $storeService = TestServiceRegister::getService(StoreService::class);
        $storeService->setMockDefaultMap([
            PaymentStates::STATE_PENDING => '1',
            PaymentStates::STATE_FAILED => '2',
            PaymentStates::STATE_CANCELLED => '3',
            PaymentStates::STATE_NEW => '4',
        ]);
        $result = StoreContext::doWithStore('1', [$this->service, 'getOrderStatusMappingSettings']);

        // assert
        self::assertEquals([
            PaymentStates::STATE_IN_PROGRESS => '',
            PaymentStates::STATE_PENDING => '1',
            PaymentStates::STATE_PAID => '',
            PaymentStates::STATE_FAILED => '2',
            PaymentStates::STATE_REFUNDED => '',
            PaymentStates::STATE_CANCELLED => '3',
            PaymentStates::STATE_PARTIALLY_REFUNDED => '',
            PaymentStates::STATE_NEW => '4',
            PaymentStates::CHARGE_BACK => ''
        ], $result);
    }

    /**
     * @throws Exception
     */
    public function testGetSettings(): void
    {
        // arrange
        $settings = [
            '1',
            '2',
            '3',
            '4',
            '5',
            '6'
        ];
        $settingsEntity = new OrderStatusMappingSettingsEntity();
        $settingsEntity->setOrderStatusMappingSettings($settings);
        $settingsEntity->setStoreId('1');
        $this->repository->save($settingsEntity);

        // act
        $result = StoreContext::doWithStore('1', [$this->service, 'getOrderStatusMappingSettings']);

        // assert
        self::assertEquals($settings, $result);
    }

    /**
     * @throws Exception
     */
    public function testGetOrderStatusMappingSettingsSetForDifferentStore(): void
    {
        // arrange
        $settings = [
            PaymentStates::STATE_IN_PROGRESS => '1',
            PaymentStates::STATE_PENDING => '2',
            PaymentStates::STATE_CANCELLED => '3',
            PaymentStates::STATE_PARTIALLY_REFUNDED => '4',
            PaymentStates::CHARGE_BACK => '5',
            PaymentStates::STATE_NEW => '6'
        ];
        $settingsEntity = new OrderStatusMappingSettingsEntity();
        $settingsEntity->setOrderStatusMappingSettings($settings);
        $settingsEntity->setStoreId('1');
        $this->repository->save($settingsEntity);

        // act
        $result = StoreContext::doWithStore('2', [$this->service, 'getOrderStatusMappingSettings']);

        // assert
        self::assertEquals([
            PaymentStates::STATE_IN_PROGRESS => '',
            PaymentStates::STATE_PENDING => '',
            PaymentStates::STATE_PAID => '',
            PaymentStates::STATE_FAILED => '',
            PaymentStates::STATE_REFUNDED => '',
            PaymentStates::STATE_CANCELLED => '',
            PaymentStates::STATE_PARTIALLY_REFUNDED => '',
            PaymentStates::STATE_NEW => '',
            PaymentStates::CHARGE_BACK => ''
        ], $result);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testSetOrderStatusMappingSettings(): void
    {
        // arrange
        $settings = [
            PaymentStates::STATE_IN_PROGRESS => '1',
            PaymentStates::STATE_PENDING => '2',
            PaymentStates::STATE_CANCELLED => '3',
            PaymentStates::STATE_PARTIALLY_REFUNDED => '4',
            PaymentStates::CHARGE_BACK => '5',
            PaymentStates::STATE_NEW => '6'
        ];

        // act
        StoreContext::doWithStore('1', [$this->service, 'saveOrderStatusMappingSettings'], [$settings]);

        // assert
        $savedEntity = $this->repository->select();
        self::assertEquals($settings, $savedEntity[0]->getOrderStatusMappingSettings());
    }

    /**
     * @throws Exception
     */
    public function testSetSettingsAlreadyExists(): void
    {
        // arrange
        $settings = [
            PaymentStates::STATE_IN_PROGRESS => '1',
            PaymentStates::STATE_PENDING => '2',
            PaymentStates::STATE_CANCELLED => '3',
            PaymentStates::STATE_PARTIALLY_REFUNDED => '4',
            PaymentStates::CHARGE_BACK => '5',
            PaymentStates::STATE_NEW => '6'
        ];
        $settingsEntity = new OrderStatusMappingSettingsEntity();
        $settingsEntity->setOrderStatusMappingSettings($settings);
        $settingsEntity->setStoreId('1');
        $this->repository->save($settingsEntity);
        $newSettings = [
            PaymentStates::STATE_IN_PROGRESS => '11',
            PaymentStates::STATE_PENDING => '22',
            PaymentStates::STATE_CANCELLED => '33',
            PaymentStates::STATE_PARTIALLY_REFUNDED => '44',
            PaymentStates::CHARGE_BACK => '55',
            PaymentStates::STATE_NEW => '66'
        ];

        // act
        StoreContext::doWithStore('1', [$this->service, 'saveOrderStatusMappingSettings'], [$newSettings]);

        // assert
        $savedEntity = $this->repository->selectOne();
        self::assertEquals($newSettings, $savedEntity->getOrderStatusMappingSettings());
    }

    /**
     * @throws Exception
     */
    public function testSetSettingsAlreadyExistsForOtherStore(): void
    {
        // arrange
        $settings = [
            PaymentStates::STATE_IN_PROGRESS => '1',
            PaymentStates::STATE_PENDING => '2',
            PaymentStates::STATE_CANCELLED => '3',
            PaymentStates::STATE_PARTIALLY_REFUNDED => '4',
            PaymentStates::CHARGE_BACK => '5',
            PaymentStates::STATE_NEW => '6'
        ];
        $settingsEntity = new OrderStatusMappingSettingsEntity();
        $settingsEntity->setOrderStatusMappingSettings($settings);
        $settingsEntity->setStoreId('1');
        $this->repository->save($settingsEntity);

        $newSettings = [
            PaymentStates::STATE_IN_PROGRESS => '11',
            PaymentStates::STATE_PENDING => '22',
            PaymentStates::STATE_CANCELLED => '33',
            PaymentStates::STATE_PARTIALLY_REFUNDED => '44',
            PaymentStates::CHARGE_BACK => '55',
            PaymentStates::STATE_NEW => '66'
        ];

        // act
        StoreContext::doWithStore('2', [$this->service, 'saveOrderStatusMappingSettings'], [$newSettings]);

        // assert
        $savedEntity = $this->repository->select();
        self::assertCount(2, $savedEntity);
        self::assertEquals($settings, $savedEntity[0]->getOrderStatusMappingSettings());
        self::assertEquals($newSettings, $savedEntity[1]->getOrderStatusMappingSettings());
    }
}
