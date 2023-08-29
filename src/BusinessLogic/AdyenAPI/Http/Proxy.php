<?php


namespace Adyen\Core\BusinessLogic\AdyenAPI\Http;

use Adyen\Core\BusinessLogic\AdyenAPI\Exceptions\HttpApiRequestException;
use Adyen\Core\BusinessLogic\AdyenAPI\Http\Requests\HttpRequest;
use Adyen\Core\Infrastructure\Http\Exceptions\HttpRequestException;
use Adyen\Core\Infrastructure\Http\HttpClient;
use Adyen\Core\Infrastructure\Http\HttpResponse;
use Adyen\Core\Infrastructure\Logger\Logger;
use Exception;

/**
 * Class Proxy
 *
 * @package Adyen\Core\BusinessLogic\AdyenAPI\Http
 */
abstract class Proxy
{
    /**
     * Http client instance.
     *
     * @var HttpClient
     */
    protected $httpClient;
    /**
     * @var string
     */
    protected $baseUrl;
    /**
     * @var string
     */
    protected $version;

    /**
     * Proxy constructor.
     *
     * @param HttpClient $httpClient
     * @param string $baseUrl Url for the API (e.g. https://management-test.adyen.com or
     * live-api-prefix-checkout-live.adyenpayments.com)
     * @param string $version The Adyen API version that should be used for constructing the API URL
     */
    public function __construct(HttpClient $httpClient, string $baseUrl, string $version)
    {
        $this->httpClient = $httpClient;
        $this->baseUrl = 'https://' . trim(str_replace(['http:', 'https:'], '', $baseUrl), '/');
        $this->version = $version;
    }

    /**
     * Performs GET HTTP request.
     *
     * @param HttpRequest $request
     *
     * @return HttpResponse Get HTTP response.
     *
     * @throws HttpRequestException
     */
    protected function get(HttpRequest $request): HttpResponse
    {
        return $this->call(HttpClient::HTTP_METHOD_GET, $request);
    }

    /**
     * Performs DELETE HTTP request.
     *
     * @param HttpRequest $request
     *
     * @return HttpResponse DELETE HTTP response.
     *
     * @throws HttpRequestException
     */
    protected function delete(HttpRequest $request): HttpResponse
    {
        return $this->call(HttpClient::HTTP_METHOD_DELETE, $request);
    }

    /**
     * Performs POST HTTP request.
     *
     * @param HttpRequest $request
     *
     * @return HttpResponse Response instance.
     *
     * @throws HttpRequestException
     */
    protected function post(HttpRequest $request): HttpResponse
    {
        return $this->call(HttpClient::HTTP_METHOD_POST, $request);
    }

    /**
     * Performs PUT HTTP request.
     *
     * @param HttpRequest $request
     *
     * @return HttpResponse Response instance.
     *
     * @throws HttpRequestException
     */
    protected function put(HttpRequest $request): HttpResponse
    {
        return $this->call(HttpClient::HTTP_METHOD_PUT, $request);
    }

    /**
     * Performs PATCH HTTP request.
     *
     * @param HttpRequest $request
     *
     * @return HttpResponse Response instance.
     *
     * @throws HttpRequestException
     */
    protected function patch(HttpRequest $request): HttpResponse
    {
        return $this->call(HttpClient::HTTP_METHOD_PATCH, $request);
    }

    /**
     * Performs HTTP call.
     *
     * @param string $method Specifies which http method is utilized in call.
     * @param HttpRequest $request
     *
     * @return HttpResponse Response instance.
     *
     * @throws HttpRequestException
     * @throws Exception
     */
    protected function call(string $method, HttpRequest $request): HttpResponse
    {
        $request->setHeaders(array_merge($request->getHeaders(), $this->getHeaders()));

        $url = $this->getRequestUrl($request);

        Logger::logDebug('Sending http '. $method . ' request, endpoint ' . $request->getEndpoint());

        $response = $this->httpClient->request(
            $method,
            $url,
            $request->getHeaders(),
            $this->getEncodedBody($request)
        );

        Logger::logDebug('Received http ' . $method . ' response with status ' .
                $response->getStatus() . ' psp reference ' . ($response->decodeBodyToArray()['pspReference'] ?? ''));

        $this->validateResponse($response);

        return $response;
    }

    /**
     * Retrieves default request headers.
     *
     * @return array Complete list of default request headers.
     */
    protected function getHeaders(): array
    {
        return [
            'Content-Type' => 'Content-Type: application/json',
            'Accept' => 'Accept: application/json',
        ];
    }

    /**
     * @param HttpRequest $request
     * @return string
     */
    protected function getEncodedBody(HttpRequest $request): string
    {
        return (string)json_encode($request->getBody());
    }

    /**
     * Retrieves full request url.
     *
     * @param HttpRequest $request
     *
     * @return string Full request url.
     */
    protected function getRequestUrl(HttpRequest $request): string
    {
        $sanitizedEndpoint = ltrim($request->getEndpoint(), '/');
        $url = "$this->baseUrl/$this->version/$sanitizedEndpoint";

        if (!empty($request->getQueries())) {
            $url .= '?' . $this->getQueryString($request);
        }

        return $url;
    }

    /**
     * Validates HTTP response.
     *
     * @param HttpResponse $response Response object to be validated.
     *
     * @throws HttpRequestException
     */
    protected function validateResponse(HttpResponse $response): void
    {
        if ($response->isSuccessful()) {
            return;
        }

        throw HttpApiRequestException::fromErrorResponse($response);
    }

    /**
     * Prepares request's queries.
     *
     * @param HttpRequest $request
     *
     * @return string
     */
    protected function getQueryString(HttpRequest $request): string
    {
        return http_build_query($request->getQueries());
    }
}
