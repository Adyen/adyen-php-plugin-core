<?php

namespace Adyen\Core\Tests\BusinessLogic\AdminAPI\TestConnection;

use Adyen\Core\BusinessLogic\AdminAPI\AdminAPI;
use Adyen\Core\BusinessLogic\AdminAPI\Connection\Request\ConnectionRequest;
use Adyen\Core\BusinessLogic\AdminAPI\TestConnection\Controller\TestConnectionController;
use Adyen\Core\BusinessLogic\Bootstrap\SingleInstance;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionSettings;
use Adyen\Core\BusinessLogic\Domain\Connection\Proxies\ConnectionProxy;
use Adyen\Core\BusinessLogic\Domain\Connection\Repositories\ConnectionSettingsRepository;
use Adyen\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use Adyen\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Adyen\Core\Infrastructure\Http\HttpClient;
use Adyen\Core\Infrastructure\Http\HttpResponse;
use Adyen\Core\Tests\BusinessLogic\AdminAPI\TestConnection\MockComponents\MockConnectionProxy;
use Adyen\Core\Tests\BusinessLogic\AdminAPI\TestConnection\MockComponents\MockConnectionSettingsRepository;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\TestHttpClient;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;
use Exception;

/**
 * Class TestConnectionApiTest
 *
 * @package Adyen\Core\Tests\BusinessLogic\AdminAPI\TestConnection
 */
class TestConnectionApiTest extends BaseTestCase
{

    /**
     * @var ConnectionProxy
     */
    private $connectionProxy;

    /**
     * @var ConnectionSettingsRepository
     */
    private $connectionSettingsRepository;

    /**
     * @var ConnectionService
     */
    private $connectionService;
    private $httpClient;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->connectionProxy = new MockConnectionProxy();
        $this->connectionSettingsRepository = new MockConnectionSettingsRepository();
        $this->connectionService = TestServiceRegister::getService(ConnectionService::class);
        $this->httpClient = new TestHttpClient();

        TestServiceRegister::registerService(
            HttpClient::class,
            function () {
                return $this->httpClient;
            }
        );

        TestServiceRegister::registerService(
            ConnectionProxy::class,
            new SingleInstance(function () {
                return $this->connectionProxy;
            })
        );

        TestServiceRegister::registerService(
            ConnectionSettingsRepository::class,
            new SingleInstance(function () {
                return $this->connectionSettingsRepository;
            })
        );
        TestServiceRegister::registerService(
            TestConnectionController::class,
            new SingleInstance(function () {
                return new TestConnectionController(
                    $this->connectionService
                );
            })
        );
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testIsResponseSuccessful(): void
    {
        // Arrange
        $this->httpClient->setMockResponses([
            new HttpResponse(200, [], file_get_contents(__DIR__ . '/../../Common/ApiResponses/me.json')),
            new HttpResponse(200, [], file_get_contents(__DIR__ . '/../../Common/ApiResponses/me.json')),
            new HttpResponse(200, [], ''),
        ]);

        // Act
        $response = AdminAPI::get()->testConnection('1')->test(
            new ConnectionRequest('1', 'test', '1234567890', '', null, null)
        );

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testIsResponseSuccessfulToArray(): void
    {
        // Arrange
        $this->httpClient->setMockResponses([
            new HttpResponse(200, [], file_get_contents(__DIR__ . '/../../Common/ApiResponses/me.json')),
            new HttpResponse(200, [], file_get_contents(__DIR__ . '/../../Common/ApiResponses/me.json')),
            new HttpResponse(200, [], ''),
        ]);

        // Act
        $response = AdminAPI::get()->testConnection('1')->test(
            new ConnectionRequest('1', 'test', '1234567890', '', null, null)
        );

        // Assert
        self::assertEquals($response->toArray(), $this->expectedResponseToArray());
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testIsResponseNotSuccessfulWithInvalidUserRoles(): void
    {
        // Arrange
        $this->httpClient->setMockResponses([
            new HttpResponse(200, [], file_get_contents(__DIR__ . '/../../Common/ApiResponses/me.json')),
            new HttpResponse(200, [], ''),
        ]);
        $this->connectionProxy->setMockRoles([]);

        // Act
        $response = AdminAPI::get()->testConnection('1')->test(
            new ConnectionRequest('1', 'test', '1234567890', '', null, null)
        );

        // Assert
        self::assertFalse($response->isSuccessful());
    }

    /**
     * @return array
     */
    private function expectedResponseToArray(): array
    {
        return [
            'status' => true,
            'message' => 'Connection with Adyen is valid'
        ];
    }
}
