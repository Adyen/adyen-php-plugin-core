<?php

namespace Adyen\Core\Tests\BusinessLogic\AdyenAPI\Management;

use Adyen\Core\BusinessLogic\AdyenAPI\Exceptions\ConnectionSettingsNotFoundException;
use Adyen\Core\BusinessLogic\AdyenAPI\Management\ProxyFactory;
use Adyen\Core\BusinessLogic\DataAccess\Connection\Entities\ConnectionSettings as ConnectionSettingsEntity;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionSettings;
use Adyen\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Adyen\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use Adyen\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use Adyen\Core\Tests\BusinessLogic\AdyenAPI\MockComponents\MockProxy;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use Exception;

class ProxyFactoryTest extends BaseTestCase
{
    public $factory;
    public $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = new ProxyFactory();
        $this->repository = TestRepositoryRegistry::getRepository(ConnectionSettingsEntity::getClassName());
    }

    /**
     * @return void
     *
     * @throws ConnectionSettingsNotFoundException
     * @throws QueryFilterInvalidParamException
     * @throws RepositoryNotRegisteredException
     * @throws Exception
     */
    public function testMakeProxyNoConnectionSettings(): void
    {
        // arrange
        $this->expectException(ConnectionSettingsNotFoundException::class);

        // act
        StoreContext::doWithStore('1', [$this->factory, 'makeProxy'],  [MockProxy::class]);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testMakeProxyModeTest(): void
    {
        // arrange
        $settings = new ConnectionSettings(
            '1',
            'test',
            new ConnectionData('1234567890', '1111'),
            null
        );
        $settingsEntity = new ConnectionSettingsEntity();
        $settingsEntity->setConnectionSettings($settings);
        $this->repository->save($settingsEntity);

        // act
        $proxy = StoreContext::doWithStore('1', [$this->factory, 'makeProxy'], [MockProxy::class]);

        // assert
        self::assertInstanceOf(MockProxy::class, $proxy);
        self::assertEquals('1234567890', $proxy->getApiKey());
        self::assertEquals('https://' . ProxyFactory::MANAGEMENT_API_TEST_URL, $proxy->getUrl());
    }

    public function testMakeProxyModeLive()
    {
        // arrange
        $settings = new ConnectionSettings(
            '1',
            'live',
            null,
            new ConnectionData('1234567890', '1111')

        );
        $settingsEntity = new ConnectionSettingsEntity();
        $settingsEntity->setConnectionSettings($settings);
        $this->repository->save($settingsEntity);

        // act
        $proxy = StoreContext::doWithStore('1', [$this->factory, 'makeProxy'], [MockProxy::class]);

        // assert
        self::assertInstanceOf(MockProxy::class, $proxy);
        self::assertEquals('1234567890', $proxy->getApiKey());
        self::assertEquals('https://' . ProxyFactory::MANAGEMENT_API_LIVE_URL, $proxy->getUrl());
    }
}
