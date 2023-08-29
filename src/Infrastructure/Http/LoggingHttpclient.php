<?php

namespace Adyen\Core\Infrastructure\Http;

use Adyen\Core\Infrastructure\Logger\Logger;

class LoggingHttpclient extends HttpClient
{
    /**
     * @var HttpClient
     */
    private $client;

    /**
     * LoggingHttpclient constructor.
     * @param HttpClient $client
     */
    public function __construct(HttpClient $client)
    {
        $this->client = $client;
    }

    /**
     * @inheritdoc Create, log and send request.
     */
    public function request($method, $url, $headers = array(), $body = '')
    {
        Logger::logDebug(
            "Sending http request to $url",
            'Core',
            array(
                'Type' => $method,
                'Endpoint' => $url,
                'Headers' => json_encode($headers),
                'Content' => $body,
            )
        );

        $response = $this->client->request($method, $url, $headers, $body);

        Logger::logDebug(
            "Http response from $url",
            'Core',
            array(
                'ResponseFor' => "$method at $url",
                'Status' => $response->getStatus(),
                'Headers' => json_encode($response->getHeaders()),
                'Content' => $response->getBody(),
            )
        );

        return $response;
    }

    /**
     * @inheritdoc Create, log and send request asynchronously.
     */
    public function requestAsync($method, $url, $headers = array(), $body = '1')
    {
        Logger::logDebug(
            "Sending async http request to $url",
            'Core',
            array(
                'Type' => $method,
                'Endpoint' => $url,
                'Headers' => json_encode($headers),
                'Content' => $body,
            )
        );

        $this->client->requestAsync($method, $url, $headers, $body);
    }

    /**
     * @inheritdoc
     */
    protected function sendHttpRequest($method, $url, $headers = array(), $body = '')
    {
        return $this->client->sendHttpRequest($method, $url, $headers, $body);
    }

    /**
     * @inheritdoc
     */
    protected function sendHttpRequestAsync($method, $url, $headers = array(), $body = '1')
    {
        $this->client->sendHttpRequestAsync($method, $url, $headers, $body);
    }
}
