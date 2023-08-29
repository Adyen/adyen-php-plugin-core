<?php

namespace Adyen\Core\BusinessLogic\AdyenAPI\Http\Authorized;

use Adyen\Core\BusinessLogic\AdyenAPI\Http\Proxy;
use Adyen\Core\Infrastructure\Http\HttpClient;

class AuthorizedProxy extends Proxy
{
    /**
     * @var string
     */
    protected $apiKey;

    public function __construct(HttpClient $httpClient, string $baseUrl, string $version, string $apiKey)
    {
        parent::__construct($httpClient, $baseUrl, $version);

        $this->apiKey = $apiKey;
    }

    /**
     * Retrieves request headers.
     *
     * @return array Complete list of request headers.
     */
    protected function getHeaders(): array
    {
        return array_merge(
            parent::getHeaders(),
            [
                'X-API-Key' => 'X-API-Key: ' . $this->apiKey,
            ]
        );
    }
}
