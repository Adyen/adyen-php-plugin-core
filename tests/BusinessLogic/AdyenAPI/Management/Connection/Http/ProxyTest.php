<?php

namespace Adyen\Core\Tests\BusinessLogic\AdyenAPI\Management\Connection\Http;

use Adyen\Core\BusinessLogic\AdyenAPI\Management\Connection\Http\Proxy;
use Adyen\Core\BusinessLogic\AdyenAPI\Management\ProxyFactory;
use Adyen\Core\BusinessLogic\DataAccess\Connection\Entities\ConnectionSettings as ConnectionSettingsEntity;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionSettings;
use Adyen\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Adyen\Core\Infrastructure\Http\Exceptions\HttpRequestException;
use Adyen\Core\Infrastructure\Http\HttpClient;
use Adyen\Core\Infrastructure\Http\HttpResponse;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\TestHttpClient;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;

class ProxyTest extends BaseTestCase
{
    /**
     * @var Proxy
     */
    public $proxy;
    /**
     * @var TestHttpClient
     */
    public $httpClient;

    protected function setUp(): void
    {
        parent::setUp();

        $repository = TestRepositoryRegistry::getRepository(ConnectionSettingsEntity::getClassName());
        $factory = new ProxyFactory();

        $settings = new ConnectionSettings(
            '1',
            'test',
            new ConnectionData('1234567890', '1111'),
            null
        );
        $settingsEntity = new ConnectionSettingsEntity();
        $settingsEntity->setConnectionSettings($settings);
        $repository->save($settingsEntity);
        $httpClient = TestServiceRegister::getService(HttpClient::class);
        $this->httpClient = $httpClient;
        TestServiceRegister::registerService(HttpClient::class, function () use ($httpClient) {
            return $httpClient;
        });

        $this->proxy = StoreContext::doWithStore('1', [$factory, 'makeProxy'], [Proxy::class]);
    }

    public function testIsApiKeyActiveUrl(): void
    {
        // arrange
        $this->httpClient->setMockResponses([new HttpResponse(200, [], '')]);

        // act
        $this->proxy->getApiCredentialDetails();

        // assert
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertEquals(
            'https://' . ProxyFactory::MANAGEMENT_API_TEST_URL . '/v1/me',
            $lastRequest['url']
        );
    }

    public function testIsApiKeyActiveMethod(): void
    {
        // arrange
        $this->httpClient->setMockResponses([new HttpResponse(200, [], '')]);

        // act
        $this->proxy->getApiCredentialDetails();

        // assert
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertEquals(HttpClient::HTTP_METHOD_GET, $lastRequest['method']);
    }

    public function testAddAllowedOriginUrl(): void
    {
        // arrange
        $this->httpClient->setMockResponses([new HttpResponse(200, [], '')]);

        // act
        $this->proxy->addAllowedOrigin('https://unit-test.test');

        // assert
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertEquals(
            'https://' . ProxyFactory::MANAGEMENT_API_TEST_URL . '/v1/me/allowedOrigins',
            $lastRequest['url']
        );
    }

    public function testAddAllowedOriginMethod(): void
    {
        // arrange
        $this->httpClient->setMockResponses([new HttpResponse(200, [], '')]);

        // act
        $this->proxy->addAllowedOrigin('https://unit-test.test');

        // assert
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertEquals(HttpClient::HTTP_METHOD_POST, $lastRequest['method']);
    }

    public function testAddAllowedOriginBody(): void
    {
        // arrange
        $this->httpClient->setMockResponses([new HttpResponse(200, [], '')]);

        // act
        $this->proxy->addAllowedOrigin('https://unit-test.test');

        // assert
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertEquals(['domain' => 'https://unit-test.test'], json_decode($lastRequest['body'], true));
    }

    /**
     * @throws HttpRequestException
     */
    public function testAddAllowedOrigin(): void
    {
        // arrange
        $this->httpClient->setMockResponses([
            new HttpResponse(
                200, array(), file_get_contents(__DIR__ . '/../../../../Common/ApiResponses/allowedOrigins.json')
            ),
        ]);

        // act
        $success = $this->proxy->addAllowedOrigin('https://unit-test.test');

        // assert
        self::assertTrue($success);
    }

    /**
     * @throws HttpRequestException
     */
    public function testAllowedOriginExists(): void
    {
        // arrange
        $this->httpClient->setMockResponses([
            new HttpResponse(
                200, array(), file_get_contents(__DIR__ . '/../../../../Common/ApiResponses/getAllowedOrigins.json')
            ),
        ]);

        // act
        $success = $this->proxy->hasAllowedOrigin('https://www.test1.com');

        // assert
        self::assertTrue($success);
    }

    /**
     * @throws HttpRequestException
     */
    public function testAllowedOriginNotExists(): void
    {
        // arrange
        $this->httpClient->setMockResponses([
            new HttpResponse(
                200, array(), file_get_contents(__DIR__ . '/../../../../Common/ApiResponses/getAllowedOrigins.json')
            ),
        ]);

        // act
        $success = $this->proxy->hasAllowedOrigin('https://www.test-no-exists.com');

        // assert
        self::assertFalse($success);
    }

    /**
     * @throws HttpRequestException
     */
    public function testAllowedOriginNotExistsWithNoOrigins(): void
    {
        // arrange
        $this->httpClient->setMockResponses([
            new HttpResponse(
                200, array(), ''
            ),
        ]);

        // act
        $success = $this->proxy->hasAllowedOrigin('https://www.test-no-exists.com');

        // assert
        self::assertFalse($success);
    }

    /**
     * @throws HttpRequestException
     */
    public function testAddAllowedOriginFail(): void
    {
        // arrange
        $this->httpClient->setMockResponses([
            new HttpResponse(
                200, array(), file_get_contents(__DIR__ . '/../../../../Common/ApiResponses/allowedOriginsFail.json')
            ),
        ]);

        // act
        $success = $this->proxy->addAllowedOrigin('https://unit-test.test');

        // assert
        self::assertFalse($success);
    }

    /**
     * @throws HttpRequestException
     */
    public function testGettingUserRoles(): void
    {
        // arrange
        $this->httpClient->setMockResponses([
            new HttpResponse(
                200, array(), file_get_contents(__DIR__ . '/../../../../Common/ApiResponses/me.json')
            ),
        ]);

        // act
        $response = $this->proxy->getUserRoles();

        // assert
        self::assertEquals($response, $this->expectedResponse());
    }

    /**
     * @throws HttpRequestException
     */
    public function testGettingUserRolesFail(): void
    {
        // arrange
        $this->httpClient->setMockResponses([
            new HttpResponse(200, [], '')]);

        // act
        $response = $this->proxy->getUserRoles();

        // assert
        self::assertEquals([], $response);
    }

    private function expectedResponse(): array
    {
        return [
            'Management API - Accounts read',
            'Management API - Webhooks read',
            'Management API - API credentials read and write',
            'Management API - Stores read',
            'Management API — Payment methods read',
            'Management API - Stores read and write',
            'Management API - Webhooks read and write',
            'Checkout encrypted cardholder data',
            'Merchant Recurring role',
            'Data Protection API',
            'Management API - Payout Account Settings Read',
            'Checkout webservice role',
            'Management API - Accounts read and write',
            'Merchant PAL Webservice role'
        ];
    }
}
