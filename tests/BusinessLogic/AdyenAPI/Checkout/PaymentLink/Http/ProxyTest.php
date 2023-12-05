<?php

namespace Adyen\Core\Tests\BusinessLogic\AdyenAPI\Checkout\PaymentLink\Http;

use Adyen\Core\BusinessLogic\AdyenAPI\Checkout\PaymentLink\Http\Proxy;
use Adyen\Core\BusinessLogic\AdyenAPI\Checkout\ProxyFactory;
use Adyen\Core\BusinessLogic\DataAccess\Connection\Entities\ConnectionSettings as ConnectionSettingsEntity;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Models\PaymentLinkRequest;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\InvalidCurrencyCode;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Currency;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\BillingAddress;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\DeliveryAddress;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\ShopperReference;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\EmptyConnectionDataException;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\EmptyStoreException;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidModeException;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionSettings;
use Adyen\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Adyen\Core\Infrastructure\Http\Exceptions\HttpRequestException;
use Adyen\Core\Infrastructure\Http\HttpClient;
use Adyen\Core\Infrastructure\Http\HttpResponse;
use Adyen\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\TestHttpClient;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;
use Exception;

/**
 * Class ProxyTest
 *
 * @package Adyen\Core\Tests\BusinessLogic\AdyenAPI\Checkout\PaymentLink\Http
 */
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
     * @return void
     *
     * @throws EmptyConnectionDataException
     * @throws EmptyStoreException
     * @throws InvalidModeException
     * @throws RepositoryNotRegisteredException
     * @throws Exception
     */
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

        $this->proxy = StoreContext::doWithStore(
            '1',
            [$factory, 'makeProxy'],
            [Proxy::class]
        );
    }

    /**
     * @return void
     *
     * @throws InvalidCurrencyCode|HttpRequestException
     */
    public function testCreatingPaymentLinkUrl(): void
    {
        // arrange
        $this->httpClient->setMockResponses([
            new HttpResponse(200, [], file_get_contents(
                __DIR__ . '/../../../../Common/ApiResponses/PaymentLink/paymentLink.json'
            ))
        ]);

        // act
        $this->proxy->createPaymentLink(
            new PaymentLinkRequest(
                'testReference',
                'testMerchantId',
                Amount::fromFloat(123.23, Currency::fromIsoCode('EUR'))
            )
        );

        // assert
        self::assertCount(1, $this->httpClient->getHistory());
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertStringContainsString('/paymentLinks', $lastRequest['url']);
        self::assertEquals('POST', $lastRequest['method']);
    }

    /**
     * @return void
     *
     * @throws InvalidCurrencyCode
     * @throws HttpRequestException
     */
    public function testStartingPaymentTransactionBody(): void
    {
        // arrange
        $this->httpClient->setMockResponses([
            new HttpResponse(200, [], file_get_contents(
                __DIR__ . '/../../../../Common/ApiResponses/PaymentLink/paymentLink.json'
            ))
        ]);

        // act
        $this->proxy->createPaymentLink(
            new PaymentLinkRequest(
                'testReference',
                'testMerchantId',
                Amount::fromFloat(123.23, Currency::fromIsoCode('EUR')),
                ['ideal', 'mc', 'visa'],
                'testCountryCode',
                ShopperReference::parse('testShopperReference'),
                'testShopperEmail',
                'testShopperLocale',
                new BillingAddress('city1', 'country1', 'house1', 'code1', 'state1', 'street1'),
                new DeliveryAddress('city2', 'country2', 'house2', 'code2', 'state2', 'street2')
            )
        );

        // assert
        $lastRequest = $this->httpClient->getLastRequest();
        $requestBody = json_decode($lastRequest['body'], true);

        self::assertNotEmpty($requestBody);
        self::assertEquals([
            'amount' => [
                'value' => 12323,
                'currency' => 'EUR',
            ],
            'merchantAccount' => 'testMerchantId',
            'reference' => 'testReference',
            'shopperEmail' => 'testShopperEmail',
            'countryCode' => 'testCountryCode',
            'shopperReference' => 'testShopperReference',
            'shopperLocale' => 'testShopperLocale',
            'billingAddress' => [
                'city' => 'city1',
                'country' => 'country1',
                'houseNumberOrName' => 'house1',
                'postalCode' => 'code1',
                'stateOrProvince' => 'state1',
                'street' => 'street1',
            ],
            'deliveryAddress' => [
                'city' => 'city2',
                'country' => 'country2',
                'houseNumberOrName' => 'house2',
                'postalCode' => 'code2',
                'stateOrProvince' => 'state2',
                'street' => 'street2',
            ],
            'allowedPaymentMethods' => ['ideal', 'mc', 'visa']
        ], $requestBody);
    }

    /**
     * @throws HttpRequestException
     * @throws InvalidCurrencyCode
     */
    public function testCreatePaymentLinkResponse(): void
    {
        // arrange
        $this->httpClient->setMockResponses([
            new HttpResponse(200, [], file_get_contents(
                __DIR__ . '/../../../../Common/ApiResponses/PaymentLink/paymentLink.json'
            ))
        ]);

        // act
        $response = $this->proxy->createPaymentLink(
            new PaymentLinkRequest(
                'testReference',
                'testMerchantId',
                Amount::fromFloat(123.23, Currency::fromIsoCode('EUR')),
                ['ideal', 'mc', 'visa'],
                'testCountryCode',
                ShopperReference::parse('testShopperReference'),
                'testShopperEmail',
                'testShopperLocale',
                new BillingAddress('city1', 'country1', 'house1', 'code1', 'state1', 'street1'),
                new DeliveryAddress('city2', 'country2', 'house2', 'code2', 'state2', 'street2')
            )
        );

        // assert
        self::assertEquals('https://test.adyen.link/PLE83C39B0A0DE0C1E', $response->getUrl());
        self::assertEquals('2022-10-28T09:16:22Z', $response->getExpiresAt());
    }
}
