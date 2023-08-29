<?php


namespace Adyen\Core\Tests\BusinessLogic\AdyenAPI\Http;


use Adyen\Core\BusinessLogic\AdyenAPI\Exceptions\HttpApiRequestException;
use Adyen\Core\BusinessLogic\AdyenAPI\Http\Requests\HttpRequest;
use Adyen\Core\Infrastructure\Http\Exceptions\HttpRequestException;
use Adyen\Core\Infrastructure\Http\HttpClient;
use Adyen\Core\Infrastructure\Http\HttpResponse;
use Adyen\Core\Tests\BusinessLogic\AdyenAPI\Http\MockComponents\MockProxy;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\TestHttpClient;

class ProxyTest extends BaseTestCase
{
    /**
     * Base URL that will be used for initializing all HTTP requests
     */
    private const BASE_PROXY_URL = 'http://test-adyen-proxy-url.domain.com/test-path';

    /**
     * @var TestHttpClient
     */
    protected $httpClient;
    /**
     * @var MockProxy
     */
    protected $proxy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->httpClient = new TestHttpClient();
        $this->proxy = new MockProxy($this->httpClient, self::BASE_PROXY_URL, 'v1');
    }


    /**
     * @throws HttpRequestException
     */
    public function testGetMethod(): void
    {
        // arrange
        $this->prepareSuccessfulResponse();

        // act
        $response = $this->proxy->get(new HttpRequest('/hello'));

        // assert
        self::assertNotNull($response);
        self::assertNotEmpty($this->httpClient->getHistory());
        $request = $this->httpClient->getLastRequest();
        self::assertEquals(HttpClient::HTTP_METHOD_GET, $request['method']);
        self::assertEquals('[]', (string)$request['body']);
        self::assertEquals('https://test-adyen-proxy-url.domain.com/test-path/v1/hello', $request['url']);
    }

    /**
     * @throws HttpRequestException
     */
    public function testDeleteMethod(): void
    {
        // arrange
        $this->prepareSuccessfulResponse();

        // act
        $response = $this->proxy->delete(new HttpRequest('/hello'));

        // assert
        self::assertNotNull($response);
        self::assertNotEmpty($this->httpClient->getHistory());
        $request = $this->httpClient->getLastRequest();
        self::assertEquals(HttpClient::HTTP_METHOD_DELETE, $request['method']);
        self::assertEquals('[]', $request['body']);
        self::assertEquals('https://test-adyen-proxy-url.domain.com/test-path/v1/hello', $request['url']);
    }

    /**
     * @throws HttpRequestException
     */
    public function testPostMethod(): void
    {
        // arrange
        $this->prepareSuccessfulResponse();
        $request = new HttpRequest('/hello');
        $body = ['test' => 123];
        $request->setBody($body);

        // act
        $response = $this->proxy->post($request);

        // assert
        self::assertNotNull($response);
        self::assertNotEmpty($this->httpClient->getHistory());
        $request = $this->httpClient->getLastRequest();
        self::assertEquals(HttpClient::HTTP_METHOD_POST, $request['method']);
        self::assertEquals(json_encode($body), $request['body']);
        self::assertEquals('https://test-adyen-proxy-url.domain.com/test-path/v1/hello', $request['url']);
    }

    /**
     * @throws HttpRequestException
     */
    public function testPutMethod(): void
    {
        // arrange
        $this->prepareSuccessfulResponse();
        $body = array('test' => 123);
        $request = new HttpRequest('/hello');
        $request->setBody($body);

        // act
        $response = $this->proxy->put($request);

        // assert
        self::assertNotNull($response);
        self::assertNotEmpty($this->httpClient->getHistory());
        $request = $this->httpClient->getLastRequest();
        self::assertEquals(HttpClient::HTTP_METHOD_PUT, $request['method']);
        self::assertEquals(json_encode($body), $request['body']);
        self::assertEquals('https://test-adyen-proxy-url.domain.com/test-path/v1/hello', $request['url']);
    }

    /**
     * @throws HttpRequestException
     */
    public function testPatchMethod(): void
    {
        // arrange
        $this->prepareSuccessfulResponse();
        $body = array('test' => 123);
        $request = new HttpRequest('/hello');
        $request->setBody($body);

        // act
        $response = $this->proxy->patch($request);

        // assert
        self::assertNotNull($response);
        self::assertNotEmpty($this->httpClient->getHistory());
        $request = $this->httpClient->getLastRequest();
        self::assertEquals(HttpClient::HTTP_METHOD_PATCH, $request['method']);
        self::assertEquals(json_encode($body), $request['body']);
        self::assertEquals('https://test-adyen-proxy-url.domain.com/test-path/v1/hello', $request['url']);
    }

    /**
     * @throws HttpRequestException
     */
    public function testPostMethodWithEmptyBody(): void
    {
        // arrange
        $this->prepareSuccessfulResponse();

        // act
        $response = $this->proxy->post(new HttpRequest('/hello'));

        // assert
        self::assertNotNull($response);
        self::assertNotEmpty($this->httpClient->getHistory());
        $request = $this->httpClient->getLastRequest();
        self::assertEquals(HttpClient::HTTP_METHOD_POST, $request['method']);
        self::assertEquals('[]', $request['body']);
        self::assertEquals('https://test-adyen-proxy-url.domain.com/test-path/v1/hello', $request['url']);
    }

    public function testDefaultRequestHeaders()
    {
        // arrange
        $this->prepareSuccessfulResponse();
        $request = new HttpRequest('/hello');

        // act
        $response = $this->proxy->post($request);

        // assert
        self::assertNotNull($response);
        self::assertNotEmpty($this->httpClient->getHistory());
        $headers = $this->httpClient->getLastRequestHeaders();
        self::assertNotEmpty($headers);
        self::assertArrayHasKey('Content-Type', $headers);
        self::assertEquals('Content-Type: application/json', $headers['Content-Type']);
        self::assertArrayHasKey('Accept', $headers);
        self::assertEquals('Accept: application/json', $headers['Accept']);
    }

    /**
     * @throws HttpRequestException
     */
    public function testAdditionalHeaders(): void
    {
        // arrange
        $this->prepareSuccessfulResponse();
        $headers = array('test' => 123);
        $request = new HttpRequest('/hello');
        $request->setHeaders($headers);

        // act
        $response = $this->proxy->get($request);

        // assert
        self::assertNotNull($response);
        self::assertNotEmpty($this->httpClient->getHistory());
        $request = $this->httpClient->getLastRequest();
        self::assertEquals(HttpClient::HTTP_METHOD_GET, $request['method']);
        self::assertEquals('[]', $request['body']);
        self::assertEquals('https://test-adyen-proxy-url.domain.com/test-path/v1/hello', $request['url']);
        $headers = $request['headers'];
        self::assertNotEmpty($headers);
        self::assertArrayHasKey('test', $headers);
        self::assertEquals(123, $headers['test']);
    }

    public function testAdditionalQueryParams(): void
    {
        // arrange
        $this->prepareSuccessfulResponse();
        $request = new HttpRequest('/hello');
        $request->setQueries(['propertyName' => 'property Value']);

        // act
        $response = $this->proxy->get($request);

        // assert
        self::assertNotNull($response);
        self::assertNotEmpty($this->httpClient->getHistory());
        $request = $this->httpClient->getLastRequest();
        self::assertEquals(HttpClient::HTTP_METHOD_GET, $request['method']);
        self::assertEquals('[]', $request['body']);
        self::assertStringEndsWith('?propertyName=property+Value', $request['url']);
    }

    /**
     * @dataProvider baseUrlProvider
     *
     * @return void
     * @throws HttpRequestException
     */
    public function testSecureUrlIsEnforced($baseUrl): void
    {
        // arrange
        $this->prepareSuccessfulResponse();

        $request = new HttpRequest('/test-query');
        $request->setQueries(['paramName' => 'paramValue']);

        $proxy = new MockProxy($this->httpClient, $baseUrl, 'v69');

        // act
        $response = $proxy->get($request);

        // assert
        self::assertNotNull($response);
        self::assertNotEmpty($this->httpClient->getHistory());
        $request = $this->httpClient->getLastRequest();
        self::assertEquals(HttpClient::HTTP_METHOD_GET, $request['method']);
        self::assertEquals('[]', (string)$request['body']);
        self::assertStringEndsWith('?paramName=paramValue', $request['url']);
    }

    public function baseUrlProvider(): array
    {
        return [
            ['url-without-scheme.some.domain.com'],
            ['http://url-with-http.some.domain.com/'],
            ['https://full-valid-url.some.domain.com/path-prefix'],
        ];
    }

    /**
     * @throws HttpRequestException
     */
    public function testFailedResponse(): void
    {
        // arrange
        $exception = null;
        $request = new HttpRequest('hello');
        $expectedResponse = $this->getFailResponse();
        $expectedResponseBody = json_decode($expectedResponse->getBody(), true);
        $this->httpClient->setMockResponses([$expectedResponse]);

        // act
        try {
            $this->proxy->get($request);
        } catch (HttpApiRequestException $exception) {}

        // assert
        self::assertNotNull($exception);
        self::assertEquals(400, $exception->getCode());
        self::assertEquals($expectedResponseBody['message'], $exception->getMessage());
        self::assertEquals($expectedResponseBody['pspReference'], $exception->getPspReference());
        self::assertEquals($expectedResponseBody['errorCode'], $exception->getErrorCode());
        self::assertEquals($expectedResponseBody['errorType'], $exception->getErrorType());
        self::assertEquals($expectedResponseBody['additionalData'], $exception->getAdditionalData());
    }

    /**
     *
     */
    protected function prepareSuccessfulResponse(): void
    {
        $this->httpClient->setMockResponses([new HttpResponse(200, [], '')]);
    }

    protected function getFailResponse(): HttpResponse
    {
        return new HttpResponse(
            400,
            [],
            json_encode([
                'additionalData' => ['someAdditionalData' => 'Additional data value', 'additional1' => 'value'],
                'errorCode' => 'test-123',
                'errorType' => 'test-error',
                'message' => 'Failed request error message',
                'pspReference' => 'ABCD123456EFG',
                'status' => 400,
            ])
        );
    }
}
