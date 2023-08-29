<?php

namespace Adyen\Core\Tests\BusinessLogic\AdyenAPI\MockComponents;

use Adyen\Core\BusinessLogic\AdyenAPI\Http\Authorized\AuthorizedProxy;
use Adyen\Core\BusinessLogic\AdyenAPI\Http\Requests\HttpRequest;
use Adyen\Core\Infrastructure\Http\HttpResponse;

class MockProxy extends AuthorizedProxy
{
    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function getUrl(): string
    {
        return $this->baseUrl;
    }

    public function testGetHttpRequest(HttpRequest $request): HttpResponse
    {
        return $this->get($request);
    }
}
