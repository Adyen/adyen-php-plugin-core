<?php

namespace Adyen\Core\Tests\BusinessLogic\AdyenAPI\Refund\Http;

use Adyen\Core\BusinessLogic\AdyenAPI\Refund\Http\Proxy;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Currency;
use Adyen\Core\BusinessLogic\Domain\Refund\Models\RefundRequest;
use Adyen\Core\Infrastructure\Http\Exceptions\HttpRequestException;
use Adyen\Core\Infrastructure\Http\HttpClient;
use Adyen\Core\Infrastructure\Http\HttpResponse;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\TestHttpClient;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class ProxyTest
 *
 * @package Adyen\Core\Tests\BusinessLogic\AdyenAPI\Refund\Http
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
    public function testCaptureUrl(): void
    {
        // arrange
        $request = new RefundRequest('psp', Amount::fromInt(1, Currency::getDefault()), 'acc');
        $this->httpClient->setMockResponses([
            new HttpResponse(
                200, array(), file_get_contents(__DIR__ . '/../../../Common/ApiResponses/Refund/refund.json')
            ),
        ]);

        // act
        $this->proxy->refundPayment($request);

        // assert
        $history = $this->httpClient->getLastRequest();
        self::assertEquals('https://checkout-test.adyen.com/v69/payments/psp/refunds', $history['url']);
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     */
    public function testCaptureMethod(): void
    {
        // arrange
        $request = new RefundRequest('psp', Amount::fromInt(1, Currency::getDefault()), 'acc');
        $this->httpClient->setMockResponses([
            new HttpResponse(
                200, array(), file_get_contents(__DIR__ . '/../../../Common/ApiResponses/Refund/refund.json')
            ),
        ]);

        // act
        $this->proxy->refundPayment($request);

        // assert
        $history = $this->httpClient->getLastRequest();
        self::assertEquals(HttpClient::HTTP_METHOD_POST, $history['method']);
    }
    /**
     * @return void
     *
     * @throws HttpRequestException
     */
    public function testRefundBody(): void
    {
        // arrange
        $request = new RefundRequest('psp', Amount::fromInt(1, Currency::getDefault()), 'acc');
        $this->httpClient->setMockResponses([
            new HttpResponse(
                200, array(), file_get_contents(__DIR__ . '/../../../Common/ApiResponses/Refund/refund.json')
            ),
        ]);

        // act
        $this->proxy->refundPayment($request);

        // assert
        $requestArray = [
            'merchantAccount' => 'acc',
            'amount' => [
                'currency' => 'EUR',
                'value' => 1,
            ]
        ];

        $history = $this->httpClient->getLastRequest();
        self::assertEquals(json_encode($requestArray), $history['body']);
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     */
    public function testCaptureSuccess(): void
    {
        // arrange
        $request = new RefundRequest('psp', Amount::fromInt(1, Currency::getDefault()), 'acc');
        $this->httpClient->setMockResponses([
            new HttpResponse(
                200, array(), file_get_contents(__DIR__ . '/../../../Common/ApiResponses/Refund/refund.json')
            ),
        ]);

        // act
       $success = $this->proxy->refundPayment($request);

        // assert
        self::assertTrue($success);
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     */
    public function testCaptureFail(): void
    {
        // arrange
        $request = new RefundRequest('psp', Amount::fromInt(1, Currency::getDefault()), 'acc');
        $this->httpClient->setMockResponses([
            new HttpResponse(
                200, array(), file_get_contents(__DIR__ . '/../../../Common/ApiResponses/Refund/failedRefund.json')
            ),
        ]);

        // act
        $success = $this->proxy->refundPayment($request);

        // assert
        self::assertFalse($success);
    }
}
