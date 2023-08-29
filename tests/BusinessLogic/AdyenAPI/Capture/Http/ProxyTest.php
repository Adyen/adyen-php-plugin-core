<?php

namespace Adyen\Core\Tests\BusinessLogic\AdyenAPI\Capture\Http;

use Adyen\Core\BusinessLogic\AdyenAPI\Capture\Http\Proxy;
use Adyen\Core\BusinessLogic\Domain\Capture\Models\CaptureRequest;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Currency;
use Adyen\Core\Infrastructure\Http\Exceptions\HttpRequestException;
use Adyen\Core\Infrastructure\Http\HttpClient;
use Adyen\Core\Infrastructure\Http\HttpResponse;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\TestHttpClient;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class ProxyTest
 *
 * @package Adyen\Core\Tests\BusinessLogic\AdyenAPI\Capture\Http
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
        $request = new CaptureRequest('psp', Amount::fromInt(1, Currency::getDefault()), 'acc');
        $this->httpClient->setMockResponses([
            new HttpResponse(
                200, array(), file_get_contents(__DIR__ . '/../../../Common/ApiResponses/Capture/capture.json')
            ),
        ]);

        // act
        $this->proxy->capturePayment($request);

        // assert
        $history = $this->httpClient->getLastRequest();
        self::assertEquals('https://checkout-test.adyen.com/v69/payments/psp/captures', $history['url']);
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     */
    public function testCaptureMethod(): void
    {
        // arrange
        $request = new CaptureRequest('psp', Amount::fromInt(1, Currency::getDefault()), 'acc');
        $this->httpClient->setMockResponses([
            new HttpResponse(
                200, array(), file_get_contents(__DIR__ . '/../../../Common/ApiResponses/Capture/capture.json')
            ),
        ]);
        // act
        $this->proxy->capturePayment($request);

        // assert
        $history = $this->httpClient->getLastRequest();
        self::assertEquals(HttpClient::HTTP_METHOD_POST, $history['method']);
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     */
    public function testCaptureBody(): void
    {
        // arrange
        $request = new CaptureRequest('psp', Amount::fromInt(1, Currency::getDefault()), 'acc');
        $this->httpClient->setMockResponses([
            new HttpResponse(
                200, array(), file_get_contents(__DIR__ . '/../../../Common/ApiResponses/Capture/capture.json')
            ),
        ]);
        // act
        $this->proxy->capturePayment($request);

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
        $request = new CaptureRequest('psp', Amount::fromInt(1, Currency::getDefault()), 'acc');
        $this->httpClient->setMockResponses([
            new HttpResponse(
                200, array(), file_get_contents(__DIR__ . '/../../../Common/ApiResponses/Capture/capture.json')
            ),
        ]);
        // act
        $success = $this->proxy->capturePayment($request);

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
        $request = new CaptureRequest('psp', Amount::fromInt(1, Currency::getDefault()), 'acc');
        $this->httpClient->setMockResponses([
            new HttpResponse(
                200, array(), file_get_contents(__DIR__ . '/../../../Common/ApiResponses/Capture/failCapture.json')
            ),
        ]);
        // act
        $success = $this->proxy->capturePayment($request);

        // assert
        self::assertFalse($success);
    }
}
