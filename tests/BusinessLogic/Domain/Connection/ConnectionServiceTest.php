<?php

namespace Adyen\Core\Tests\BusinessLogic\Domain\Connection;

use Adyen\Core\BusinessLogic\DataAccess\Connection\Entities\ConnectionSettings as ConnectionSettingsEntity;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFountException;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidApiKeyException;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\UserDoesNotHaveNecessaryRolesException;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionSettings;
use Adyen\Core\BusinessLogic\Domain\Connection\Proxies\ConnectionProxy;
use Adyen\Core\BusinessLogic\Domain\Connection\Repositories\ConnectionSettingsRepository;
use Adyen\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use Adyen\Core\BusinessLogic\Domain\Integration\Store\StoreService;
use Adyen\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Adyen\Core\BusinessLogic\Domain\Webhook\Models\WebhookConfig;
use Adyen\Core\BusinessLogic\Domain\Webhook\Repositories\WebhookConfigRepository;
use Adyen\Core\BusinessLogic\Webhook\Repositories\OrderStatusMappingRepository;
use Adyen\Core\Infrastructure\Http\HttpClient;
use Adyen\Core\Infrastructure\Http\HttpResponse;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Adyen\Core\Tests\BusinessLogic\Common\MockComponents\MockWebhookConfigRepository;
use Adyen\Core\Tests\BusinessLogic\Domain\MockComponents\MockConnectionProxySuccess;
use Adyen\Core\Tests\BusinessLogic\Domain\MockComponents\MockStoreService;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\ORM\MemoryRepository;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\TestHttpClient;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;
use Exception;

class ConnectionServiceTest extends BaseTestCase
{
    /**
     * @var ConnectionService
     */
    public $service;
    /**
     * @var TestHttpClient
     */
    public $httpClient;
    /**
     * @var MemoryRepository
     */
    public $repository;
    /**
     * @var ConnectionProxy
     */
    public $proxy;
    /**
     * @var ConnectionSettingsRepository
     */
    public $connectionSettingsRepository;
    /**
     * @var OrderStatusMappingRepository
     */
    public $orderStatusMappingRepository;
    /**
     * @var StoreService
     */
    public $storeService;

    /**
     * @var WebhookConfigRepository
     */
    public $webhookConfigRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $httpClient = new TestHttpClient();
        $this->httpClient = $httpClient;
        TestServiceRegister::registerService(
            HttpClient::class,
            function () {
                return $this->httpClient;
            }
        );
        $this->proxy = new MockConnectionProxySuccess();
        TestServiceRegister::registerService(
            ConnectionProxy::class,
            function () {
                return $this->proxy;
            }
        );
        $this->storeService = new  MockStoreService();
        TestServiceRegister::registerService(
            StoreService::class,
            function () {
                return $this->storeService;
            }
        );
        $this->connectionSettingsRepository = TestServiceRegister::getService(ConnectionSettingsRepository::class);
        $this->repository = TestRepositoryRegistry::getRepository(ConnectionSettingsEntity::getClassName());
        $this->service = TestServiceRegister::getService(ConnectionService::class);
        $this->orderStatusMappingRepository = TestServiceRegister::getService(OrderStatusMappingRepository::class);
        $this->webhookConfigRepository = new MockWebhookConfigRepository();
    }

    public function testSaveConnectionDataApiKeyValid(): void
    {
        // arrange
        $this->httpClient->setMockResponses([
            new HttpResponse(200, [], file_get_contents(__DIR__ . '/../../Common/ApiResponses/me.json')),
            new HttpResponse(200, [], file_get_contents(__DIR__ . '/../../Common/ApiResponses/me.json')),
            new HttpResponse(200, [], ''),
            new HttpResponse(200, [], file_get_contents(__DIR__ . '/../../Common/ApiResponses/getAllowedOrigins.json')),
            new HttpResponse(200, [], file_get_contents(__DIR__ . '/../../Common/ApiResponses/allowedOrigins.json'))
        ]);
        $settings = new ConnectionSettings(
            '1',
            'test',
            new ConnectionData('1234567890', ''),
            null
        );

        // act
        StoreContext::doWithStore('1', [$this->service, 'saveConnectionData'], [$settings]);

        // assert
        $savedEntity = $this->repository->selectOne();
        self::assertEquals($settings, $savedEntity->getConnectionSettings());
    }

    /**
     * @throws Exception
     */
    public function testSaveConnectionDataApiKeyInvalid(): void
    {
        // arrange
        $this->httpClient->setMockResponses([
            new HttpResponse(200, [], ''),
        ]);

        $settings = new ConnectionSettings(
            '1',
            'test',
            new ConnectionData('1234567890', ''),
            null
        );
        $this->expectException(InvalidApiKeyException::class);
        $this->service = TestServiceRegister::getService(ConnectionService::class);

        // act
        StoreContext::doWithStore('1', [$this->service, 'saveConnectionData'], [$settings]);
    }

    /**
     * @throws Exception
     */
    public function testUserRolesInvalid(): void
    {
        // arrange
        $this->httpClient->setMockResponses([
            new HttpResponse(200, [], file_get_contents(__DIR__ . '/../../Common/ApiResponses/missingRoles.json')),
            new HttpResponse(200, [], ''),
            new HttpResponse(200, [], file_get_contents(__DIR__ . '/../../Common/ApiResponses/missingRoles.json')),
            new HttpResponse(200, [], file_get_contents(__DIR__ . '/../../Common/ApiResponses/getAllowedOrigins.json')),
            new HttpResponse(200, [], file_get_contents(__DIR__ . '/../../Common/ApiResponses/allowedOriginsFail.json'))
        ]);

        $settings = new ConnectionSettings(
            '1',
            'test',
            new ConnectionData('1234567890', '123'),
            null
        );
        $this->expectException(UserDoesNotHaveNecessaryRolesException::class);
        $this->service = TestServiceRegister::getService(ConnectionService::class);

        // act
        StoreContext::doWithStore('1', [$this->service, 'saveConnectionData'], [$settings]);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testReRegisterWebhooksNoConnectionData(): void
    {
        // arrange
        $this->expectException(ConnectionSettingsNotFountException::class);

        // act
        StoreContext::doWithStore('1', [$this->service, 'reRegisterWebhook']);

        // assert
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testReRegisterWebhooks(): void
    {
        // arrange
        TestServiceRegister::registerService(WebhookConfigRepository::class,
            function () {
                return $this->webhookConfigRepository;
            }
        );

        $this->webhookConfigRepository->setWebhookConfig(
            new WebhookConfig(
                '1',
                'merchant1',
                true,
                'username',
                'password',
                'hmac')
        );
        $this->httpClient->setMockResponses([
            new HttpResponse(200, [], file_get_contents(__DIR__ . '/../../Common/ApiResponses/me.json')),
            new HttpResponse(200, [], file_get_contents(__DIR__ . '/../../Common/ApiResponses/Webhook/webhooks.json')),
            new HttpResponse(200, [], ''),
            new HttpResponse(200, [],
                file_get_contents(__DIR__ . '/../../Common/ApiResponses/Webhook/registerWebhook.json')),
            new HttpResponse(200, [], file_get_contents(__DIR__ . '/../../Common/ApiResponses/hmac.json')),
            new HttpResponse(200, [],
                file_get_contents(__DIR__ . '/../../Common/ApiResponses/Webhook/testWebhook.json'))
        ]);
        $settings = new ConnectionSettings(
            '1',
            'test',
            new ConnectionData('1234567890', '123'),
            null
        );
        $this->connectionSettingsRepository->setConnectionSettings($settings);

       TestServiceRegister::registerService(ConnectionService::class,
        function () {
            return new ConnectionService(
               $this->connectionSettingsRepository,
                $this->storeService,
                $this->webhookConfigRepository
            );
        });
       $this->service = TestServiceRegister::getService(ConnectionService::class);
        // act
        StoreContext::doWithStore('1', [$this->service, 'reRegisterWebhook']);

        // assert
        self::assertEquals($this->webhookConfigRepository->getWebhookConfig()->getHmac(),
            '7052E6804F0AF40DCC390464C817F4F963516FA42AC8816D518DC5D39F41E902');
    }
}
