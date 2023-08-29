<?php

namespace Adyen\Core\Tests\BusinessLogic\AdyenAPI\Management\Merchant\Http;

use Adyen\Core\BusinessLogic\AdyenAPI\Management\Merchant\Http\Proxy;
use Adyen\Core\BusinessLogic\AdyenAPI\Management\ProxyFactory;
use Adyen\Core\BusinessLogic\DataAccess\Connection\Entities\ConnectionSettings as ConnectionSettingsEntity;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionSettings;
use Adyen\Core\BusinessLogic\Domain\Merchant\Models\Merchant;
use Adyen\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Adyen\Core\Infrastructure\Http\HttpClient;
use Adyen\Core\Infrastructure\Http\HttpResponse;
use Adyen\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\TestHttpClient;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;
use Exception;

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

    /**
     * @throws RepositoryNotRegisteredException
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $repository = TestRepositoryRegistry::getRepository(ConnectionSettingsEntity::getClassName());
        $factory    = new ProxyFactory();

        $settings       = new ConnectionSettings(
            '1',
            'test',
            new ConnectionData('1234567890', '1111'),
            null
        );
        $settingsEntity = new ConnectionSettingsEntity();
        $settingsEntity->setConnectionSettings($settings);
        $repository->save($settingsEntity);
        $httpClient       = TestServiceRegister::getService(HttpClient::class);
        $this->httpClient = $httpClient;
        TestServiceRegister::registerService(HttpClient::class, function () use ($httpClient) {
            return $httpClient;
        });

        $this->proxy = StoreContext::doWithStore('1', [$factory, 'makeProxy'], [Proxy::class]);
    }

    /**
     * @return void
     */
    public function testMerchantUrl(): void
    {
        // arrange
        $this->httpClient->setMockResponses([new HttpResponse(200, [], '')]);

        // act
        $this->proxy->getMerchants();

        // assert
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertEquals(
            'https://' . ProxyFactory::MANAGEMENT_API_TEST_URL . '/v1/merchants',
            $lastRequest['url']
        );
    }

    /**
     * @return void
     */
    public function testMerchantsMethod(): void
    {
        // arrange
        $this->httpClient->setMockResponses([new HttpResponse(200, [], '')]);

        // act
        $this->proxy->getMerchants();

        // assert
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertEquals(HttpClient::HTTP_METHOD_GET, $lastRequest['method']);
    }

    public function testMerchantsResponse(): void
    {
        // arrange
        $this->httpClient->setMockResponses([
            new HttpResponse(
                200, array(), file_get_contents(__DIR__ . '/../../../../Common/ApiResponses/merchants.json')
            ),
        ]);

        // act
        $merchants = $this->proxy->getMerchants();

        // assert
        self::assertEquals($merchants, $this->expectedResponse());
    }


    public function testMerchantsInvalidResponse(): void
    {
        // arrange
        $this->httpClient->setMockResponses([
            new HttpResponse(
                200, array(), file_get_contents(__DIR__ . '/../../../../Common/ApiResponses/merchantsInvalid.json')
            ),
        ]);

        // act
        $merchants = $this->proxy->getMerchants();

        // assert
        self::assertEquals($merchants, $this->expectedInvalidResponse());
    }

    public function testGetMerchantByIdUrl(): void
    {
        // arrange
        $this->httpClient->setMockResponses([
            new HttpResponse(
                200, array(), file_get_contents(__DIR__ . '/../../../../Common/ApiResponses/merchant.json')
            ),
        ]);

        // act
        $this->proxy->getMerchantById('1234');

        // assert
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertEquals(
            'https://' . ProxyFactory::MANAGEMENT_API_TEST_URL . '/v1/merchants/1234',
            $lastRequest['url']
        );
    }

    public function testGetMerchantByIdMethod(): void
    {
        // arrange
        $this->httpClient->setMockResponses([
            new HttpResponse(
                200, array(), file_get_contents(__DIR__ . '/../../../../Common/ApiResponses/merchant.json')
            ),
        ]);

        // act
        $this->proxy->getMerchantById('1234');

        // assert
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertEquals(HttpClient::HTTP_METHOD_GET, $lastRequest['method']);
    }

    public function testGenerateClientKeyUrl(): void
    {
        // arrange
        $this->httpClient->setMockResponses([
            new HttpResponse(
                200, array(), file_get_contents(__DIR__ . '/../../../../Common/ApiResponses/clientKey.json')
            ),
        ]);

        // act
        $this->proxy->generateClientKey();

        // assert
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertEquals(
            'https://' . ProxyFactory::MANAGEMENT_API_TEST_URL . '/v1/me/generateClientKey',
            $lastRequest['url']
        );
    }

    public function testGenerateClientKeyMethod(): void
    {
        // arrange
        $this->httpClient->setMockResponses([
            new HttpResponse(
                200, array(), file_get_contents(__DIR__ . '/../../../../Common/ApiResponses/clientKey.json')
            ),
        ]);

        // act
        $this->proxy->generateClientKey();

        // assert
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertEquals(HttpClient::HTTP_METHOD_POST, $lastRequest['method']);
    }

    /**
     * @return Merchant[]
     */
    private function expectedResponse(): array
    {
        return [
            new Merchant('LogeecomECOM', 'LogeecomECOM', '', 'Logeecom'),
            new Merchant('Logeecom_LogeecomECOM1_TEST', 'Logeecom_LogeecomECOM1_TEST', '', 'Logeecom'),
            new Merchant('Logeecom_Sava_local_test_TEST', 'Logeecom_Sava_local_test_TEST', '', 'Logeecom'),
        ];
    }

    /**
     * @return Merchant[]
     */
    private function expectedInvalidResponse(): array
    {
        return [
            new Merchant('Logeecom_Sava_local_test_TEST', 'Logeecom_Sava_local_test_TEST', '', 'Logeecom')
        ];
    }
}
