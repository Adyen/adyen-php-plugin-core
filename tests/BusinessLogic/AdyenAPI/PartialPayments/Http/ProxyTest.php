<?php

namespace Adyen\Core\Tests\BusinessLogic\AdyenAPI\PartialPayments\Http;

use Adyen\Core\BusinessLogic\AdyenAPI\PartialPayments\Http\Proxy;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Currency;
use Adyen\Core\BusinessLogic\Domain\PartialPayments\Models\BalanceRequest;
use Adyen\Core\BusinessLogic\Domain\PartialPayments\Models\Order;
use Adyen\Core\BusinessLogic\Domain\PartialPayments\Models\OrderCancelRequest;
use Adyen\Core\BusinessLogic\Domain\PartialPayments\Models\OrderCreateRequest;
use Adyen\Core\Infrastructure\Http\HttpClient;
use Adyen\Core\Infrastructure\Http\HttpResponse;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;
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

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->httpClient = new TestHttpClient();
        TestServiceRegister::registerService(HttpClient::class, function () {
            return $this->httpClient;
        });

        $this->proxy = new Proxy($this->httpClient, 'https://checkout-test.adyen.com', 'v71', '0123456789');
    }

    public function testBalanceUrl()
    {
        // arrange
        $request = new BalanceRequest(
            [
                'type' => 'givex',
                'number' => '4126491073027401',
                'cvc' => '737'
            ],
            'YOUR_MERCHANT_ACCOUNT',
            Amount::fromInt(10000, Currency::fromIsoCode('EUR'))
        );
        $this->httpClient->setMockResponses([
            new HttpResponse(
                200, array(), file_get_contents(__DIR__ . '/../../../Common/ApiResponses/PartialPayments/balance.json')
            ),
        ]);

        // act
        $this->proxy->getBalance($request);

        // assert
        $history = $this->httpClient->getLastRequest();
        self::assertEquals('https://checkout-test.adyen.com/v71/paymentMethods/balance', $history['url']);
    }

    public function testBalanceMethod()
    {
        // arrange
        $request = new BalanceRequest(
            [
                'type' => 'givex',
                'number' => '4126491073027401',
                'cvc' => '737'
            ],
            'YOUR_MERCHANT_ACCOUNT',
            Amount::fromInt(10000, Currency::fromIsoCode('EUR'))
        );
        $this->httpClient->setMockResponses([
            new HttpResponse(
                200, array(), file_get_contents(__DIR__ . '/../../../Common/ApiResponses/PartialPayments/balance.json')
            ),
        ]);

        // act
        $this->proxy->getBalance($request);

        // assert
        $history = $this->httpClient->getLastRequest();
        self::assertEquals(HttpClient::HTTP_METHOD_POST, $history['method']);
    }

    public function testBalanceBody()
    {
        // arrange
        $request = new BalanceRequest(
            [
                'type' => 'givex',
                'number' => '4126491073027401',
                'cvc' => '737'
            ],
            'YOUR_MERCHANT_ACCOUNT',
            Amount::fromInt(10000, Currency::fromIsoCode('EUR'))
        );
        $this->httpClient->setMockResponses([
            new HttpResponse(
                200, array(), file_get_contents(__DIR__ . '/../../../Common/ApiResponses/PartialPayments/balance.json')
            ),
        ]);

        // act
        $this->proxy->getBalance($request);

        // assert
        $requestArray = [
            'paymentMethod' => [
                'type' => 'givex',
                'number' => '4126491073027401',
                'cvc' => '737'
            ],
            'amount' => [
                'value' => 10000,
                'currency' => 'EUR',
            ],
            'merchantAccount' => 'YOUR_MERCHANT_ACCOUNT',
        ];

        $history = $this->httpClient->getLastRequest();
        self::assertEquals(json_encode($requestArray), $history['body']);
    }

    public function testCreateOrderUrl()
    {
        // arrange
        $request = new OrderCreateRequest(
            'YOUR_ORDER_REFERENCE',
            'YOUR_MERCHANT_ACCOUNT',
            Amount::fromInt(10000, Currency::fromIsoCode('EUR'))
        );
        $this->httpClient->setMockResponses([
            new HttpResponse(
                200, array(), file_get_contents(__DIR__ . '/../../../Common/ApiResponses/PartialPayments/order.json')
            ),
        ]);

        // act
        $this->proxy->createOrder($request);

        // assert
        $history = $this->httpClient->getLastRequest();
        self::assertEquals('https://checkout-test.adyen.com/v71/orders', $history['url']);
    }

    public function testCreateOrderMethod()
    {
        // arrange
        $request = new OrderCreateRequest(
            'YOUR_ORDER_REFERENCE',
            'YOUR_MERCHANT_ACCOUNT',
            Amount::fromInt(10000, Currency::fromIsoCode('EUR'))
        );
        $this->httpClient->setMockResponses([
            new HttpResponse(
                200, array(), file_get_contents(__DIR__ . '/../../../Common/ApiResponses/PartialPayments/order.json')
            ),
        ]);

        // act
        $this->proxy->createOrder($request);

        // assert
        $history = $this->httpClient->getLastRequest();
        self::assertEquals(HttpClient::HTTP_METHOD_POST, $history['method']);
    }

    public function testCreateOrderBody()
    {
        // arrange
        $request = new OrderCreateRequest(
            'YOUR_ORDER_REFERENCE',
            'YOUR_MERCHANT_ACCOUNT',
            Amount::fromInt(10000, Currency::fromIsoCode('EUR'))
        );
        $this->httpClient->setMockResponses([
            new HttpResponse(
                200, array(), file_get_contents(__DIR__ . '/../../../Common/ApiResponses/PartialPayments/order.json')
            ),
        ]);

        // act
        $this->proxy->createOrder($request);

        // assert
        $requestArray = [
            'reference' => 'YOUR_ORDER_REFERENCE',
            'amount' =>
                [
                    'value' => 10000,
                    'currency' => 'EUR',
                ],
            'merchantAccount' => 'YOUR_MERCHANT_ACCOUNT',
        ];

        $history = $this->httpClient->getLastRequest();
        self::assertEquals(json_encode($requestArray), $history['body']);
    }

    public function testCancelOrderUrl()
    {
        // arrange
        $request = new OrderCancelRequest(
            'YOUR_MERCHANT_ACCOUNT',
            new Order('BQABAgCxXvknCldOcRElkxY8Za7iyym4Wv8aDzyNwmj', '8815517812932012')
        );
        $this->httpClient->setMockResponses([
            new HttpResponse(
                200, array(), file_get_contents(__DIR__ . '/../../../Common/ApiResponses/PartialPayments/cancelOrder.json')
            ),
        ]);

        // act
        $this->proxy->cancelOrder($request);

        // assert
        $history = $this->httpClient->getLastRequest();
        self::assertEquals('https://checkout-test.adyen.com/v71/orders/cancel', $history['url']);
    }

    public function testCancelOrderMethod()
    {
        // arrange
        $request = new OrderCancelRequest(
            'YOUR_MERCHANT_ACCOUNT',
            new Order('BQABAgCxXvknCldOcRElkxY8Za7iyym4Wv8aDzyNwmj', '8815517812932012')
        );
        $this->httpClient->setMockResponses([
            new HttpResponse(
                200, array(), file_get_contents(__DIR__ . '/../../../Common/ApiResponses/PartialPayments/cancelOrder.json')
            ),
        ]);

        // act
        $this->proxy->cancelOrder($request);

        // assert
        $history = $this->httpClient->getLastRequest();
        self::assertEquals(HttpClient::HTTP_METHOD_POST, $history['method']);
    }

    public function testCancelOrderBody()
    {
        // arrange
        $request = new OrderCancelRequest(
            'YOUR_MERCHANT_ACCOUNT',
            new Order('BQABAgCxXvknCldOcRElkxY8Za7iyym4Wv8aDzyNwmj', '8815517812932012')
        );
        $this->httpClient->setMockResponses([
            new HttpResponse(
                200, array(), file_get_contents(__DIR__ . '/../../../Common/ApiResponses/PartialPayments/cancelOrder.json')
            ),
        ]);

        // act
        $this->proxy->cancelOrder($request);

        // assert
        $requestArray = [
            'order' => [
                'pspReference' => '8815517812932012',
                'orderData' => 'BQABAgCxXvknCldOcRElkxY8Za7iyym4Wv8aDzyNwmj'
            ],
            'merchantAccount' => 'YOUR_MERCHANT_ACCOUNT',
        ];

        $history = $this->httpClient->getLastRequest();
        self::assertEquals(json_encode($requestArray), $history['body']);
    }
}