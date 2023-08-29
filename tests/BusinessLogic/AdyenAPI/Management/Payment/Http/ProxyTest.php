<?php

namespace Adyen\Core\Tests\BusinessLogic\AdyenAPI\Management\Payment\Http;

use Adyen\Core\BusinessLogic\AdyenAPI\Management\Payment\Http\Proxy;
use Adyen\Core\BusinessLogic\AdyenAPI\Management\ProxyFactory;
use Adyen\Core\BusinessLogic\DataAccess\Connection\Entities\ConnectionSettings as ConnectionSettingsEntity;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionSettings;
use Adyen\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\PaymentMethodResponse;
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
        TestServiceRegister::registerService(HttpClient::class, function () {
            return $this->httpClient;
        });

        $this->proxy = StoreContext::doWithStore('1', [$factory, 'makeProxy'], [Proxy::class]);
    }

    public function testGetAvailablePaymentMethodsUrl(): void
    {
        // arrange
        $this->httpClient->setMockResponses([
            new HttpResponse(
                200,
                [],
                file_get_contents(
                    __DIR__ . '/../../../../Common/ApiResponses/PaymentMethods/managementPaymentMethods.json'
                )
            ),
            new HttpResponse(200, [],
                json_encode(
                    [
                        'itemsTotal' => 47,
                        'pagesTotal' => 0,
                        'data' => []
                    ]
                )
            )
        ]);

        // act
        $this->proxy->getAvailablePaymentMethods('1234');

        // assert
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertEquals(
            'https://management-test.adyen.com/v1/merchants/1234/paymentMethodSettings?pageNumber=2&pageSize=100',
            $lastRequest['url']
        );
    }

    public function testGetAvailablePaymentMethodsMethod(): void
    {
        // arrange
        $this->httpClient->setMockResponses([
            new HttpResponse(
                200,
                [],
                file_get_contents(
                    __DIR__ . '/../../../../Common/ApiResponses/PaymentMethods/managementPaymentMethods.json'
                )
            ),
            new HttpResponse(200, [],
                json_encode(
                    [
                        'itemsTotal' => 47,
                        'pagesTotal' => 0,
                        'data' => []
                    ]
                )
            )
        ]);

        // act
        $this->proxy->getAvailablePaymentMethods('1234');

        // assert
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertEquals(HttpClient::HTTP_METHOD_GET, $lastRequest['method']);
    }

    public function testGetAvailablePaymentMethodsResponse(): void
    {
        // arrange
        $this->httpClient->setMockResponses([
            new HttpResponse(
                200,
                [],
                file_get_contents(
                    __DIR__ . '/../../../../Common/ApiResponses/PaymentMethods/managementPaymentMethods.json'
                )
            ),
            new HttpResponse(200, [],
                json_encode(
                    [
                        'itemsTotal' => 47,
                        'pagesTotal' => 0,
                        'data' => []
                    ]
                ))
        ]);
        $expectedResponse = [
            new PaymentMethodResponse(
                'PM3224P22322225FR8FHP6DQF',
                'alipay',
                true,
                ['ANY'],
                ["CHF", "HKD", "EUR", "DKK", "USD", "MYR", "CAD", "NOK", "CNY", "THB", "AUD", "SGD",
                    "JPY", "GBP", "CZK", "SEK", "NZD", "RUB"]
            ),
            new PaymentMethodResponse(
                'PM3224P22322225FNZ7B8G595',
                'amex',
                true,
                ['ANY'],
                ['ANY']
            ),
            new PaymentMethodResponse(
                'PM3224R223224K5FRJX6FDD2M',
                'amex_applepay',
                true,
                ['ANY'],
                ['ANY']
            )
        ];

        // act
        $response = $this->proxy->getAvailablePaymentMethods('1234');

        // assert
        self::assertEquals($expectedResponse, $response);
    }
}
