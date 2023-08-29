<?php

namespace Adyen\Core\Tests\BusinessLogic\AdminAPI\Connection;

use Adyen\Core\BusinessLogic\AdminAPI\Connection\Controller\ConnectionController;
use Adyen\Core\BusinessLogic\AdminAPI\Connection\Request\ConnectionRequest;
use Adyen\Core\BusinessLogic\AdminAPI\Connection\Response\ConnectionSettingsResponse;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ApiCredentials;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionSettings;
use Adyen\Core\BusinessLogic\Domain\Connection\Proxies\ConnectionProxy;
use Adyen\Core\BusinessLogic\Domain\Connection\Repositories\ConnectionSettingsRepository;
use Adyen\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use Adyen\Core\BusinessLogic\Domain\Merchant\Proxies\MerchantProxy;
use Adyen\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Adyen\Core\BusinessLogic\Domain\Webhook\Proxies\WebhookProxy;
use Adyen\Core\BusinessLogic\Domain\Webhook\Repositories\WebhookConfigRepository;
use Adyen\Core\Infrastructure\Http\HttpClient;
use Adyen\Core\Infrastructure\Http\HttpResponse;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Adyen\Core\Tests\BusinessLogic\Domain\Merchant\MockComponents\MockMerchantProxy;
use Adyen\Core\Tests\BusinessLogic\Domain\MockComponents\MockConnectionProxySuccess;
use Adyen\Core\Tests\BusinessLogic\Domain\Webhook\Mocks\MockWebhookProxy;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\TestHttpClient;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;

class ConnectionControllerTest extends BaseTestCase
{
    /**
     * @var ConnectionSettingsRepository
     */
    public $connectionSettingsRepository;
    /**
     * @var WebhookConfigRepository
     */
    public $webhookConfigRepository;
    public $merchantProxy;
    public $webhookProxy;
    public $controller;
    public $httpClient;

    protected function setUp(): void
    {
        parent::setUp();

        TestServiceRegister::registerService(
            ConnectionProxy::class,
            static function () {
                return new MockConnectionProxySuccess();
            }
        );
        $this->connectionSettingsRepository = TestServiceRegister::getService(ConnectionSettingsRepository::class);
        $this->webhookConfigRepository = TestServiceRegister::getService(WebhookConfigRepository::class);
        $this->httpClient = new TestHttpClient();
        $merchantProxy = new MockMerchantProxy(
            $this->httpClient, 'test.url', 'V1', '0123456789'
        );
        TestServiceRegister::registerService(HttpClient::class, function () {
            return $this->httpClient;
        });
        $this->merchantProxy = $merchantProxy;
        TestServiceRegister::registerService(MerchantProxy::class, static function () use ($merchantProxy) {
            return $merchantProxy;
        });
        $webhookProxy = new MockWebhookProxy();
        $this->webhookProxy = $webhookProxy;
        TestServiceRegister::registerService(WebhookProxy::class, static function () use ($webhookProxy) {
            return $webhookProxy;
        });
        $this->controller = new ConnectionController(TestServiceRegister::getService(ConnectionService::class));
    }

    public function testConnectionInitialization(): void
    {
        // arrange
        $this->httpClient->setMockResponses([
            new HttpResponse(200, [], file_get_contents(__DIR__ . '/../../Common/ApiResponses/me.json')),
            new HttpResponse(200, [], file_get_contents(__DIR__ . '/../../Common/ApiResponses/me.json')),
            new HttpResponse(200, [], ''),
            new HttpResponse(
                200, array(), file_get_contents(__DIR__ . '/../../Common/ApiResponses/getAllowedOrigins.json')
            ),
            new HttpResponse(
                200, array(), file_get_contents(__DIR__ . '/../../Common/ApiResponses/allowedOrigins.json')
            ),
        ]);
        $this->webhookProxy->testResponse = file_get_contents(__DIR__ . '/../../Common/ApiResponses/Webhook/testWebhook.json');
        $apiCredentials = new ApiCredentials('2345', true, 'test');
        $connectionSettings = new ConnectionSettings(
            '1',
            'test',
            new ConnectionData('012345678', '', '', '1234', $apiCredentials),
            null
        );
        $this->connectionSettingsRepository->setConnectionSettings($connectionSettings);
        $connectionRequest = new ConnectionRequest(
            '1', 'test', '012345678', 'asdf', null, null
        );

        // act
        StoreContext::doWithStore('1', [$this->controller, 'connect'], [$connectionRequest]);

        // assert
        $webhookConfig = StoreContext::doWithStore('1', [$this->webhookConfigRepository, 'getWebhookConfig']);
        self::assertEquals('username', $webhookConfig->getUsername());
        self::assertEquals('0123456789', $webhookConfig->getHmac());
    }

    public function testConnectionInitializationWebhookRegistrationFails(): void
    {
        // arrange
        $this->httpClient->setMockResponses([
            new HttpResponse(200, [], file_get_contents(__DIR__ . '/../../Common/ApiResponses/me.json')),
            new HttpResponse(200, [], file_get_contents(__DIR__ . '/../../Common/ApiResponses/me.json')),
            new HttpResponse(200, [], ''),
            new HttpResponse(
                200, array(), file_get_contents(__DIR__ . '/../../Common/ApiResponses/getAllowedOrigins.json')
            ),
            new HttpResponse(
                200, array(), file_get_contents(__DIR__ . '/../../Common/ApiResponses/allowedOrigins.json')
            ),
        ]);
        $apiCredentials = new ApiCredentials('2345', true, 'test');
        $connectionSettings = new ConnectionSettings(
            '1',
            'test',
            new ConnectionData('012345678', '', '', '1234', $apiCredentials),
            null
        );
        $this->connectionSettingsRepository->setConnectionSettings($connectionSettings);
        $connectionRequest = new ConnectionRequest(
            '1', 'test', '012345678', 'asdf', null, null
        );
        $this->webhookProxy->registerWebhookFails = true;
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to register webhook.');

        // act
        StoreContext::doWithStore('1', [$this->controller, 'connect'], [$connectionRequest]);
    }

    public function testConnectionInitializationHmacGenerationFails(): void
    {
        // arrange
        $this->httpClient->setMockResponses([
            new HttpResponse(200, [], file_get_contents(__DIR__ . '/../../Common/ApiResponses/me.json')),
            new HttpResponse(200, [], file_get_contents(__DIR__ . '/../../Common/ApiResponses/me.json')),
            new HttpResponse(200, [], ''),
            new HttpResponse(
                200, array(), file_get_contents(__DIR__ . '/../../Common/ApiResponses/getAllowedOrigins.json')
            ),
            new HttpResponse(
                200, array(), file_get_contents(__DIR__ . '/../../Common/ApiResponses/allowedOrigins.json')
            ),
        ]);
        $apiCredentials = new ApiCredentials('2345', true, 'test');
        $connectionSettings = new ConnectionSettings(
            '1',
            'test',
            new ConnectionData('012345678', '', '', '1234', $apiCredentials),
            null
        );
        $this->connectionSettingsRepository->setConnectionSettings($connectionSettings);
        $connectionRequest = new ConnectionRequest(
            '1', 'test', '012345678', 'asdf', null, null
        );
        $this->webhookProxy->generateHmacFails = true;
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Error generating HMAC.');
        $this->webhookProxy->testResponse = file_get_contents(__DIR__ . '/../../Common/ApiResponses/Webhook/testWebhook.json');

        // act
        StoreContext::doWithStore('1', [$this->controller, 'connect'], [$connectionRequest]);
    }

    public function testConnectionInitializationMerchantDoesNotExist(): void
    {
        // arrange
        $this->httpClient->setMockResponses([
            new HttpResponse(200, [], file_get_contents(__DIR__ . '/../../Common/ApiResponses/me.json')),
            new HttpResponse(200, [], file_get_contents(__DIR__ . '/../../Common/ApiResponses/me.json')),
            new HttpResponse(200, [], ''),
            new HttpResponse(
                200, array(), file_get_contents(__DIR__ . '/../../Common/ApiResponses/getAllowedOrigins.json')
            ),
            new HttpResponse(
                200, array(), file_get_contents(__DIR__ . '/../../Common/ApiResponses/allowedOrigins.json')
            ),
        ]);
        $apiCredentials = new ApiCredentials('2345', true, 'test');
        $connectionSettings = new ConnectionSettings(
            '1',
            'test',
            new ConnectionData('012345678', '', '', '1234', $apiCredentials),
            null
        );
        $this->connectionSettingsRepository->setConnectionSettings($connectionSettings);
        $connectionRequest = new ConnectionRequest(
            '1', 'test', '012345678', 'asdf', null, null
        );
        $this->merchantProxy->getMerchantFails = true;
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to register webhook.');

        // act
        StoreContext::doWithStore('1', [$this->controller, 'connect'], [$connectionRequest]);
    }

    public function testGetConnectionSettingsNoSettings(): void
    {
        // act
        $result = StoreContext::doWithStore('1', [$this->controller, 'getConnectionSettings']);

        // arrange
        self::assertEquals(new ConnectionSettingsResponse(null), $result);
    }

    public function testGetConnectionSettings(): void
    {
        // arrange
        $connectionSettings = new ConnectionSettings(
            '1',
            'test',
            new ConnectionData('012345678', '', '', '1234', new ApiCredentials('123', true, 'test')),
            null
        );
        $this->connectionSettingsRepository->setConnectionSettings($connectionSettings);

        // act
        $result = StoreContext::doWithStore('1', [$this->controller, 'getConnectionSettings']);

        // assert
        self::assertEquals(new ConnectionSettingsResponse($connectionSettings), $result);
    }
}
