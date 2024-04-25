<?php

namespace Adyen\Core\Tests\BusinessLogic\AdyenAPI\AuthorizationAdjustment\Http;

use Adyen\Core\BusinessLogic\AdyenAPI\AuthorizationAdjustment\Http\Proxy;
use Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Models\AuthorizationAdjustmentRequest;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Currency;
use Adyen\Core\Infrastructure\Http\Exceptions\HttpRequestException;
use Adyen\Core\Infrastructure\Http\HttpClient;
use Adyen\Core\Infrastructure\Http\HttpResponse;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\TestHttpClient;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class ProxyTest.
 *
 * @package Adyen\Core\Tests\BusinessLogic\AdyenAPI\AuthorizationAdjustment\Http
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
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->httpClient = new TestHttpClient();
        TestServiceRegister::registerService(HttpClient::class, function () {
            return $this->httpClient;
        });

        $this->proxy = new Proxy($this->httpClient, 'https://checkout-test.adyen.com', 'v69', '0123456789');
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     */
    public function testAdjustmentUrl(): void
    {
        // arrange
        $request = new AuthorizationAdjustmentRequest(
            'test_psp_reference',
            Amount::fromInt(1, Currency::getDefault()),
            'merchantAccount',
            'reference');
        $this->httpClient->setMockResponses([
            new HttpResponse(
                200, array(),
                file_get_contents(__DIR__ . '/../../../Common/ApiResponses/AuthorizationAdjustment/adjustment.json')
            ),
        ]);

        // act
        $this->proxy->adjustPayment($request);

        // assert
        $history = $this->httpClient->getLastRequest();
        self::assertEquals('https://checkout-test.adyen.com/v69/payments/test_psp_reference/amountUpdates',
            $history['url']);
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     */
    public function testAdjustmentMethod(): void
    {
        // arrange
        $request = new AuthorizationAdjustmentRequest(
            'test_psp_reference',
            Amount::fromInt(1, Currency::getDefault()),
            'merchantAccount',
            'reference');
        $this->httpClient->setMockResponses([
            new HttpResponse(
                200, array(),
                file_get_contents(__DIR__ . '/../../../Common/ApiResponses/AuthorizationAdjustment/adjustment.json')
            ),
        ]);
        // act
        $this->proxy->adjustPayment($request);

        // assert
        $history = $this->httpClient->getLastRequest();
        self::assertEquals(HttpClient::HTTP_METHOD_POST, $history['method']);
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     */
    public function testAdjustmentBody(): void
    {
        // arrange
        $request = new AuthorizationAdjustmentRequest(
            'test_psp_reference',
            Amount::fromInt(1, Currency::getDefault()),
            'merchantAccount',
            'reference');
        $this->httpClient->setMockResponses([
            new HttpResponse(
                200, array(),
                file_get_contents(__DIR__ . '/../../../Common/ApiResponses/AuthorizationAdjustment/adjustment.json')
            ),
        ]);
        // act
        $this->proxy->adjustPayment($request);

        // assert
        $requestArray = [
            'merchantAccount' => 'merchantAccount',
            'amount' => [
                'currency' => 'EUR',
                'value' => 1,
            ],
            'industryUsage' => 'delayedCharge',
            'reference' => 'reference'
        ];

        $history = $this->httpClient->getLastRequest();
        self::assertEquals(json_encode($requestArray), $history['body']);
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     */
    public function testAdjustmentSuccess(): void
    {
        // arrange
        $request = new AuthorizationAdjustmentRequest(
            'test_psp_reference',
            Amount::fromInt(1, Currency::getDefault()),
            'merchantAccount',
            'reference');
        $this->httpClient->setMockResponses([
            new HttpResponse(
                200, array(),
                file_get_contents(__DIR__ . '/../../../Common/ApiResponses/AuthorizationAdjustment/adjustment.json')
            ),
        ]);
        // act
        $success = $this->proxy->adjustPayment($request);

        // assert
        self::assertTrue($success);
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     */
    public function testAdjustmentFail(): void
    {
        // arrange
        $request = new AuthorizationAdjustmentRequest(
            'test_psp_reference',
            Amount::fromInt(1, Currency::getDefault()),
            'merchantAccount',
            'reference');
        $this->httpClient->setMockResponses([
            new HttpResponse(
                200, array(),
                file_get_contents(__DIR__ . '/../../../Common/ApiResponses/AuthorizationAdjustment/adjustmentFail.json')
            ),
        ]);
        // act
        $success = $this->proxy->adjustPayment($request);

        // assert
        self::assertFalse($success);
    }
}
