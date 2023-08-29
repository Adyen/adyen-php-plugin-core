<?php


namespace Adyen\Core\Tests\BusinessLogic\AdyenAPI\Http\MockComponents;

use Adyen\Core\BusinessLogic\AdyenAPI\Http\Proxy;
use Adyen\Core\BusinessLogic\AdyenAPI\Http\Requests\HttpRequest;
use Adyen\Core\Infrastructure\Http\HttpResponse;

class MockProxy extends Proxy
{
    public function get(HttpRequest $request): HttpResponse
    {
        return parent::get($request);
    }

    public function delete(HttpRequest $request): HttpResponse
    {
        return parent::delete($request);
    }

    public function put(HttpRequest $request): HttpResponse
    {
        return parent::put($request);
    }

    public function post(HttpRequest $request): HttpResponse
    {
        return parent::post($request);
    }

    public function patch(HttpRequest $request): HttpResponse
    {
        return parent::patch($request);
    }
}
