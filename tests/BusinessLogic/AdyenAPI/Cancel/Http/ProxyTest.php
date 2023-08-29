<?php

namespace Adyen\Core\Tests\BusinessLogic\AdyenAPI\Cancel\Http;

use Adyen\Core\BusinessLogic\AdyenAPI\Cancel\Http\Proxy;
use Adyen\Core\BusinessLogic\Domain\Cancel\Models\CancelRequest;
use Adyen\Core\Infrastructure\Http\Exceptions\HttpRequestException;
use Adyen\Core\Infrastructure\Http\HttpClient;
use Adyen\Core\Infrastructure\Http\HttpResponse;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\TestHttpClient;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class ProxyTest
 *
 * @package Adyen\Core\Tests\BusinessLogic\AdyenAPI\Cancel\Http
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
    public function testCancelUrl(): void
    {
        // arrange
        $request = new CancelRequest('test_psp_reference', 'merchantReference', 'acc');
        $this->httpClient->setMockResponses([
            new HttpResponse(
                200, array(), file_get_contents(__DIR__ . '/../../../Common/ApiResponses/Cancel/cancel.json')
            ),
        ]);

        // act
        $this->proxy->cancelPayment($request);

        // assert
        $history = $this->httpClient->getLastRequest();
        self::assertEquals('https://checkout-test.adyen.com/v69/payments/test_psp_reference/cancels', $history['url']);
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     */
    public function testCancelMethod(): void
    {
        // arrange
        $request = new CancelRequest('test_psp_reference', 'merchantReference', 'acc');
        $this->httpClient->setMockResponses([
            new HttpResponse(
                200, array(), file_get_contents(__DIR__ . '/../../../Common/ApiResponses/Cancel/cancel.json')
            ),
        ]);

        // act
        $this->proxy->cancelPayment($request);

        // assert
        $history = $this->httpClient->getLastRequest();
        self::assertEquals(HttpClient::HTTP_METHOD_POST, $history['method']);
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     */
    public function testCancelBody(): void
    {
        // arrange
        $request = new CancelRequest('test_psp_reference', 'merchantReference', 'acc');
        $this->httpClient->setMockResponses([
            new HttpResponse(
                200, array(), file_get_contents(__DIR__ . '/../../../Common/ApiResponses/Cancel/cancel.json')
            ),
        ]);

        // act
        $this->proxy->cancelPayment($request);

        // assert
        $requestArray = [
            'merchantAccount' => 'acc',
            'reference' => 'merchantReference'
        ];

        $history = $this->httpClient->getLastRequest();
        self::assertEquals(json_encode($requestArray), $history['body']);
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     */
    public function testCancelSuccess(): void
    {
        // arrange
        $request = new CancelRequest('test_psp_reference', 'merchantReference', 'acc');
        $this->httpClient->setMockResponses([
            new HttpResponse(
                200, array(), file_get_contents(__DIR__ . '/../../../Common/ApiResponses/Cancel/cancel.json')
            ),
        ]);

        // act
        $success = $this->proxy->cancelPayment($request);

        // assert
        self::assertTrue($success);
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     */
    public function testCancelFail(): void
    {
        // arrange
        $request = new CancelRequest('test_psp_reference', 'merchantReference', 'acc');
        $this->httpClient->setMockResponses([
            new HttpResponse(
                200, array(), file_get_contents(__DIR__ . '/../../../Common/ApiResponses/Cancel/failCancel.json')
            ),
        ]);

        // act
        $success = $this->proxy->cancelPayment($request);

        // assert
        self::assertFalse($success);
    }
}
