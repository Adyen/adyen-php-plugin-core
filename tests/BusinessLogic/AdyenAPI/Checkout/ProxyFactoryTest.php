<?php

namespace Adyen\Core\Tests\BusinessLogic\AdyenAPI\Checkout;

use Adyen\Core\BusinessLogic\AdyenAPI\Checkout\ProxyFactory;
use Adyen\Core\BusinessLogic\AdyenAPI\Exceptions\ConnectionSettingsNotFoundException;
use Adyen\Core\BusinessLogic\AdyenAPI\Http\Requests\HttpRequest;
use Adyen\Core\BusinessLogic\DataAccess\Connection\Entities\ConnectionSettings as ConnectionSettingsEntity;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionSettings;
use Adyen\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Adyen\Core\Infrastructure\Http\HttpClient;
use Adyen\Core\Infrastructure\Http\HttpResponse;
use Adyen\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use Adyen\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use Adyen\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use Adyen\Core\Tests\BusinessLogic\AdyenAPI\MockComponents\MockProxy;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\TestHttpClient;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;
use Exception;

class ProxyFactoryTest extends BaseTestCase
{
    /**
     * @var ProxyFactory
     */
    public $factory;
    /**
     * @var RepositoryInterface
     */
    public $repository;
    /**
     * @var TestHttpClient
     */
    public $httpClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = new ProxyFactory();
        $this->repository = TestRepositoryRegistry::getRepository(ConnectionSettingsEntity::getClassName());
        $this->httpClient = TestServiceRegister::getService(HttpClient::class);
        TestServiceRegister::registerService(HttpClient::class, function () {
            return $this->httpClient;
        });
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
        self::assertEquals('https://checkout-test.adyen.com', $proxy->getUrl());
    }

    public function testMakeProxyModeLive()
    {
        // arrange
        $settings = new ConnectionSettings(
            '1',
            'live',
            null,
            new ConnectionData('1234567890', '1111', 'live-key')

        );
        $settingsEntity = new ConnectionSettingsEntity();
        $settingsEntity->setConnectionSettings($settings);
        $this->repository->save($settingsEntity);

        // act
        $proxy = StoreContext::doWithStore('1', [$this->factory, 'makeProxy'], [MockProxy::class]);

        // assert
        self::assertInstanceOf(MockProxy::class, $proxy);
        self::assertEquals('1234567890', $proxy->getApiKey());
        self::assertEquals('https://live-key-checkout-live.adyenpayments.com/checkout', $proxy->getUrl());
    }

    public function testProxyVersion()
    {
        // arrange
        $this->httpClient->setMockResponses([new HttpResponse(200, [], '')]);
        $settings = new ConnectionSettings(
            '1',
            'live',
            null,
            new ConnectionData('1234567890', '1111','live-key')

        );
        $settingsEntity = new ConnectionSettingsEntity();
        $settingsEntity->setConnectionSettings($settings);
        $this->repository->save($settingsEntity);
        /** @var MockProxy $proxy */
        $proxy = StoreContext::doWithStore('1', [$this->factory, 'makeProxy'], [MockProxy::class]);

        // act
        $proxy->testGetHttpRequest(new HttpRequest('test'));

        // assert
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertNotNull($lastRequest);
        self::assertArrayHasKey('url', $lastRequest);
        self::assertStringStartsWith('https://live-key-checkout-live.adyenpayments.com/checkout/v69/', $lastRequest['url']);
    }
}
