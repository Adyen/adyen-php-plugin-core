<?php

namespace Adyen\Core\Tests\BusinessLogic\AdminAPI\Integration;

use Adyen\Core\BusinessLogic\AdminAPI\AdminAPI;
use Adyen\Core\BusinessLogic\AdminAPI\Integration\Controller\IntegrationController;
use Adyen\Core\BusinessLogic\AdminAPI\Integration\Response\StateResponse;
use Adyen\Core\BusinessLogic\Bootstrap\SingleInstance;
use Adyen\Core\BusinessLogic\DataAccess\Connection\Entities\ConnectionSettings as ConnectionSettingsEntity;
use Adyen\Core\BusinessLogic\Domain\Connection\Enums\Mode;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ApiCredentials;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionSettings;
use Adyen\Core\BusinessLogic\Domain\Connection\Proxies\ConnectionProxy;
use Adyen\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use Adyen\Core\Infrastructure\Http\HttpClient;
use Adyen\Core\Infrastructure\Http\HttpResponse;
use Adyen\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use Adyen\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use Adyen\Core\Tests\BusinessLogic\AdminAPI\TestConnection\MockComponents\MockConnectionProxy;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\TestHttpClient;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;
use Exception;

class IntegrationApiTest extends BaseTestCase
{
    /**
     * @var RepositoryInterface
     */
    private $repository;
    private $httpClient;

    /**
     * @throws RepositoryNotRegisteredException
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->repository = TestRepositoryRegistry::getRepository(ConnectionSettingsEntity::getClassName());
        $this->connectionProxy = new MockConnectionProxy();
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
            IntegrationController::class,
            new SingleInstance(function () {
                return new IntegrationController(TestServiceRegister::getService(ConnectionService::class));
            })
        );
    }

    /**
     * @throws Exception
     */
    public function testStateWithoutCredentials(): void
    {
        // Act
        $state = AdminAPI::get()->integration('store123')->getState();

        // Assert
        self::assertEquals(StateResponse::onboarding(), $state);
    }

    /**
     * @throws Exception
     */
    public function testStateWithCredentials(): void
    {
        // Arrange
        $this->httpClient->setMockResponses([
            new HttpResponse(200, [], file_get_contents(__DIR__ . '/../../Common/ApiResponses/me.json')),
            new HttpResponse(200, [], file_get_contents(__DIR__ . '/../../Common/ApiResponses/me.json')),
            new HttpResponse(200, [], ''),
        ]);
        $settings = new ConnectionSettings(
            'store123',
            Mode::MODE_TEST,
            new ConnectionData('1234567890', '1111', '', '', new ApiCredentials('123', true, 'test')),
            null
        );
        $settingsEntity = new ConnectionSettingsEntity();
        $settingsEntity->setConnectionSettings($settings);
        $this->repository->save($settingsEntity);

        // Act
        $state = AdminAPI::get()->integration('store123')->getState();

        // Assert
        self::assertEquals(StateResponse::dashboard(), $state);
    }

    /**
     * @throws Exception
     */
    public function testWithWrongCredentials(): void
    {
        // Arrange
        $settings = new ConnectionSettings(
            'store123',
            Mode::MODE_TEST,
            new ConnectionData('1234567890', ''),
            null
        );
        $settingsEntity = new ConnectionSettingsEntity();
        $settingsEntity->setConnectionSettings($settings);
        $this->repository->save($settingsEntity);

        // Act
        $state = AdminAPI::get()->integration('store124')->getState();

        // Assert
        self::assertEquals(StateResponse::onboarding(), $state);
    }

    /**
     * @throws Exception
     */
    public function testWithMultipleCredentials(): void
    {
        // Arrange
        $this->httpClient->setMockResponses([
            new HttpResponse(200, [], file_get_contents(__DIR__ . '/../../Common/ApiResponses/me.json')),
            new HttpResponse(200, [], file_get_contents(__DIR__ . '/../../Common/ApiResponses/me.json')),
            new HttpResponse(200, [], ''),
        ]);
        $settings = new ConnectionSettings(
            'store123',
            Mode::MODE_TEST,
            new ConnectionData('1234567890', '1111', '', '', new ApiCredentials('123', true, 'test')),
            null
        );
        $settingsEntity = new ConnectionSettingsEntity();
        $settingsEntity->setConnectionSettings($settings);
        $this->repository->save($settingsEntity);

        $settings = new ConnectionSettings(
            'store124',
            Mode::MODE_TEST,
            new ConnectionData('1234567899', '222', '', '', new ApiCredentials('321', true, 'test')),
            null
        );
        $settingsEntity = new ConnectionSettingsEntity();
        $settingsEntity->setConnectionSettings($settings);
        $this->repository->save($settingsEntity);

        // Act
        $state = AdminAPI::get()->integration('store124')->getState();

        // Assert
        self::assertEquals(StateResponse::dashboard(), $state);
    }

    /**
     * @throws Exception
     */
    public function testWithMultipleWrongCredentials(): void
    {
        // Arrange
        $settings = new ConnectionSettings(
            'store123',
            Mode::MODE_TEST,
            new ConnectionData('1234567890', ''),
            null
        );
        $settingsEntity = new ConnectionSettingsEntity();
        $settingsEntity->setConnectionSettings($settings);
        $this->repository->save($settingsEntity);

        $settings = new ConnectionSettings(
            'store124',
            Mode::MODE_TEST,
            new ConnectionData('1234567899', '222'),
            null
        );
        $settingsEntity = new ConnectionSettingsEntity();
        $settingsEntity->setConnectionSettings($settings);
        $this->repository->save($settingsEntity);

        // Act
        $state = AdminAPI::get()->integration('store125')->getState();

        // Assert
        self::assertEquals(StateResponse::onboarding(), $state);
    }

    public function testExceptionHandling(): void
    {
        // Arrange
        TestRepositoryRegistry::cleanUp();

        // Act
        $response = AdminAPI::get()->integration('store125')->getState();

        // Assert
        $rawResponse = $response->toArray();
        self::assertFalse($response->isSuccessful());
        self::assertArrayHasKey('errorCode', $rawResponse);
        self::assertArrayHasKey('errorMessage', $rawResponse);
        self::assertStringContainsString(ConnectionSettingsEntity::class, $rawResponse['errorMessage']);
        self::assertStringEndsWith('not found or registered.', $rawResponse['errorMessage']);
    }
}
