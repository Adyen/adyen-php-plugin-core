<?php

namespace Adyen\Core\Tests\BusinessLogic\AdyenAPI\Checkout\Payments\Http;

use Adyen\Core\BusinessLogic\AdyenAPI\Checkout\Payments\Http\Proxy;
use Adyen\Core\BusinessLogic\AdyenAPI\Checkout\ProxyFactory;
use Adyen\Core\BusinessLogic\DataAccess\Connection\Entities\ConnectionSettings as ConnectionSettingsEntity;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Currency;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\AvailablePaymentMethodsResponse;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Country;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PaymentMethodResponse;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PaymentMethodsRequest;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PaymentRequest;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\ResultCode;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\ShopperReference;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\UpdatePaymentDetailsRequest;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionSettings;
use Adyen\Core\BusinessLogic\Domain\Multistore\StoreContext;
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

    public function testStartingPaymentTransactionUrl(): void
    {
        // arrange
        $this->httpClient->setMockResponses([
            new HttpResponse(200, [], file_get_contents(
                __DIR__ . '/../../../../Common/ApiResponses/PaymentRequest/authorisedPaymentTransaction.json'
            ))
        ]);

        // act
        $this->proxy->startPaymentTransaction(new PaymentRequest(
            Amount::fromFloat(123.23, Currency::fromIsoCode('EUR')),
            'testMerchantId',
            'testReference',
            'https://test.example.com/return/url?test=123',
            []
        ));

        // assert
        self::assertCount(1, $this->httpClient->getHistory());
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertStringContainsString('/payments', $lastRequest['url']);
        self::assertEquals('POST', $lastRequest['method']);
    }

    public function testStartingPaymentTransactionBody(): void
    {
        // arrange
        $this->httpClient->setMockResponses([
            new HttpResponse(200, [], file_get_contents(
                __DIR__ . '/../../../../Common/ApiResponses/PaymentRequest/authorisedPaymentTransaction.json'
            ))
        ]);

        // act
        $this->proxy->startPaymentTransaction(new PaymentRequest(
            Amount::fromFloat(123.23, Currency::fromIsoCode('USD')),
            'testMerchantId',
            'testReference',
            'https://test.example.com/return/url?test=123',
            []
        ));

        // assert
        $lastRequest = $this->httpClient->getLastRequest();
        $requestBody = json_decode($lastRequest['body'], true);

        self::assertNotEmpty($requestBody);
        self::assertEquals([
            'amount' => [
                'value' => 12323,
                'currency' => 'USD',
            ],
            'channel' => '',
            'origin' => '',
            'merchantAccount' => 'testMerchantId',
            'reference' => 'testReference',
            'returnUrl' => 'https://test.example.com/return/url?test=123',
            'paymentMethod' => [],
            'dateOfBirth' => '',
            'telephoneNumber' => '',
            'shopperEmail' => '',
            'countryCode' => '',
            'socialSecurityNumber' => '',
            'storePaymentMethod' => false,
            'conversionId' => '',
            'shopperReference' => '',
            'shopperLocale' => '',
        ], $requestBody);
    }

    public function testStartingPaymentTransactionAuthorisedResponse(): void
    {
        // arrange
        $rawResponseBody = file_get_contents(
            __DIR__ . '/../../../../Common/ApiResponses/PaymentRequest/authorisedPaymentTransaction.json'
        );
        $this->httpClient->setMockResponses([new HttpResponse(200, [], $rawResponseBody)]);

        // act
        $response = $this->proxy->startPaymentTransaction(new PaymentRequest(
            Amount::fromFloat(123, Currency::fromIsoCode('JPY')),
            'testMerchantId',
            'testReference',
            'https://test.example.com/return/url?test=123',
            []
        ));

        // assert
        $responseBody = json_decode($rawResponseBody, true);
        self::assertEquals(ResultCode::authorised(), $response->getResultCode());
        self::assertNull($response->getAction());
        self::assertEquals($responseBody['pspReference'], $response->getPspReference());
    }

    public function testStartingPaymentTransactionRedirectResponse(): void
    {
        // arrange
        $rawResponseBody = file_get_contents(
            __DIR__ . '/../../../../Common/ApiResponses/PaymentRequest/redirectPaymentTransaction.json'
        );
        $this->httpClient->setMockResponses([new HttpResponse(200, [], $rawResponseBody)]);

        // act
        $response = $this->proxy->startPaymentTransaction(new PaymentRequest(
            Amount::fromInt(123, Currency::fromIsoCode('EUR')),
            'testMerchantId',
            'testReference',
            'https://test.example.com/return/url?test=123',
            []
        ));

        // assert
        $responseBody = json_decode($rawResponseBody, true);
        self::assertEquals(ResultCode::redirectShopper(), $response->getResultCode());
        self::assertEquals($responseBody['action'], $response->getAction());
        self::assertNull($response->getPspReference());
    }

    public function testUpdatePaymentDetailsUrl(): void
    {
        // arrange
        $this->httpClient->setMockResponses([
            new HttpResponse(200, [], file_get_contents(
                __DIR__ . '/../../../../Common/ApiResponses/PaymentRequest/paymentDetailsResponse.json'
            ))
        ]);

        // act
        $this->proxy->updatePaymentDetails(UpdatePaymentDetailsRequest::parse([]));

        // assert
        self::assertCount(1, $this->httpClient->getHistory());
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertStringContainsString('/payments/details', $lastRequest['url']);
        self::assertEquals('POST', $lastRequest['method']);
    }

    public function testUpdatePaymentDetailsBody(): void
    {
        // arrange
        $this->httpClient->setMockResponses([
            new HttpResponse(200, [], file_get_contents(
                __DIR__ . '/../../../../Common/ApiResponses/PaymentRequest/paymentDetailsResponse.json'
            ))
        ]);

        // act
        $this->proxy->updatePaymentDetails(UpdatePaymentDetailsRequest::parse([
            'details' => [
                'unsupportedKey' => 'some value',
                'redirectResult' => 'test-redirect-result-value',
            ]
        ]));

        // assert
        $lastRequest = $this->httpClient->getLastRequest();
        $requestBody = json_decode($lastRequest['body'], true);

        self::assertNotEmpty($requestBody);
        self::assertEquals([
            'details' => [
                'redirectResult' => 'test-redirect-result-value',
            ],
        ], $requestBody);
    }

    public function testUpdatePaymentDetailsBodyWithPaymentData(): void
    {
        // arrange
        $this->httpClient->setMockResponses([
            new HttpResponse(200, [], file_get_contents(
                __DIR__ . '/../../../../Common/ApiResponses/PaymentRequest/paymentDetailsResponse.json'
            ))
        ]);

        // act
        $this->proxy->updatePaymentDetails(UpdatePaymentDetailsRequest::parse([
            'details' => [
                'unsupportedKey' => 'some value',
                'redirectResult' => 'test-redirect-result-value',
            ],
            'paymentData' => 'test_payment_data'
        ]));

        // assert
        $lastRequest = $this->httpClient->getLastRequest();
        $requestBody = json_decode($lastRequest['body'], true);

        self::assertNotEmpty($requestBody);
        self::assertEquals([
            'details' => [
                'redirectResult' => 'test-redirect-result-value',
            ],
            'paymentData' => 'test_payment_data'
        ], $requestBody);
    }

    public function testUpdatePaymentDetailsResponse(): void
    {
        // arrange
        $rawResponseBody = file_get_contents(
            __DIR__ . '/../../../../Common/ApiResponses/PaymentRequest/paymentDetailsResponse.json'
        );
        $this->httpClient->setMockResponses([new HttpResponse(200, [], $rawResponseBody)]);

        // act
        $response = $this->proxy->updatePaymentDetails(UpdatePaymentDetailsRequest::parse([]));

        // assert
        $responseBody = json_decode($rawResponseBody, true);
        self::assertEquals($responseBody['resultCode'], (string)$response->getResultCode());
        self::assertEquals($responseBody['pspReference'], $response->getPspReference());
    }

    public function testGetAvailablePaymentMethodsUrl(): void
    {
        // arrange
        $rawResponseBody = file_get_contents(
            __DIR__ . '/../../../../Common/ApiResponses/PaymentMethods/paymentMethods.json'
        );
        $this->httpClient->setMockResponses([new HttpResponse(200, [], $rawResponseBody)]);

        // act
        $this->proxy->getAvailablePaymentMethods(new PaymentMethodsRequest(
                '1234',
                [
                    'visacheckout',
                    'vvvcadeaubon',
                    'webshopgiftcard',
                    'wefashiongiftcard',
                    'westernunion',
                    'winkelcheque',
                    'yourgift'
                ]
            )
        );

        // assert
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertEquals('https://checkout-test.adyen.com/v69/paymentMethods', $lastRequest['url']);
    }

    public function testGetAvailablePaymentMethodsMethod(): void
    {
        // arrange
        $rawResponseBody = file_get_contents(
            __DIR__ . '/../../../../Common/ApiResponses/PaymentMethods/paymentMethods.json'
        );
        $this->httpClient->setMockResponses([new HttpResponse(200, [], $rawResponseBody)]);

        // act
        $this->proxy->getAvailablePaymentMethods(new PaymentMethodsRequest(
                '1234',
                [
                    'visacheckout',
                    'vvvcadeaubon',
                    'webshopgiftcard',
                    'wefashiongiftcard',
                    'westernunion',
                    'winkelcheque',
                    'yourgift'
                ]
            )
        );

        // assert
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertEquals(HttpClient::HTTP_METHOD_POST, $lastRequest['method']);
    }

    public function testGetAvailablePaymentMethodsResponse(): void
    {
        // arrange
        $rawResponseBody = file_get_contents(
            __DIR__ . '/../../../../Common/ApiResponses/PaymentMethods/paymentMethods.json'
        );
        $this->httpClient->setMockResponses([new HttpResponse(200, [], $rawResponseBody)]);
        $expectedResponse = [
            new PaymentMethodResponse(
                'Visa Checkout',
                'visacheckout',
                [
                    'details' => [
                        0 => [
                            'key' => 'additionalData.visacheckout.callId',
                            'type' => 'text'
                        ]
                    ],
                    'name' => 'Visa Checkout',
                    'type' => 'visacheckout'
                ]),
            new PaymentMethodResponse(
                'VVV Cadeaubon',
                'vvvcadeaubon',
                [
                    'name' => 'VVV Cadeaubon',
                    'brand' => 'vvvcadeaubon',
                    "type" => "giftcard"
                ]
            ),
            new PaymentMethodResponse(
                'Webshop Giftcard',
                'webshopgiftcard',
                [
                    'name' => 'Webshop Giftcard',
                    'brand' => 'webshopgiftcard',
                    "type" => "giftcard"
                ]
            ),
            new PaymentMethodResponse(
                'Winkel Cheque',
                'winkelcheque',
                [
                    'name' => 'Winkel Cheque',
                    'brand' => 'winkelcheque',
                    "type" => "giftcard"
                ]
            ),
            new PaymentMethodResponse(
                'Your Gift',
                'yourgift',
                [
                    'name' => 'Your Gift',
                    'brand' => 'yourgift',
                    "type" => "giftcard"
                ]
            ),
        ];

        // act
        $response = $this->proxy->getAvailablePaymentMethods(new PaymentMethodsRequest(
                '1234',
                [
                    'visacheckout',
                    'vvvcadeaubon',
                    'webshopgiftcard',
                    'winkelcheque',
                    'yourgift'
                ]
            )
        );

        // assert
        self::assertEquals(new AvailablePaymentMethodsResponse($expectedResponse), $response);
    }

    public function testGetAvailablePaymentMethodsFiltering(): void
    {
        // arrange
        $rawResponseBody = file_get_contents(
            __DIR__ . '/../../../../Common/ApiResponses/PaymentMethods/paymentMethods.json'
        );
        $this->httpClient->setMockResponses([new HttpResponse(200, [], $rawResponseBody)]);
        $allowedPaymentMethodsFilter = [
            'visacheckout',
            'vvvcadeaubon',
        ];

        // act
        $this->proxy->getAvailablePaymentMethods(new PaymentMethodsRequest(
                '1234',
                $allowedPaymentMethodsFilter,
                Amount::fromInt(123456, Currency::getDefault()),
                Country::fromIsoCode('de'),
                'de-DE',
                ShopperReference::parse('test-123456')
            )
        );

        // assert
        $lastRequest = $this->httpClient->getLastRequest();
        $requestBody = json_decode($lastRequest['body'], true);
        self::assertEquals('1234', $requestBody['merchantAccount']);
        self::assertEquals(['visacheckout', 'vvvcadeaubon', 'giftcard'], $requestBody['allowedPaymentMethods']);
        self::assertEquals(['value' => 123456, 'currency' => 'EUR'], $requestBody['amount']);
        self::assertEquals('DE', $requestBody['countryCode']);
        self::assertEquals('de-DE', $requestBody['shopperLocale']);
        self::assertEquals('test-123456', $requestBody['shopperReference']);
    }
}
