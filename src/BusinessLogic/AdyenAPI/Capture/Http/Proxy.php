<?php

namespace Adyen\Core\BusinessLogic\AdyenAPI\Capture\Http;

use Adyen\Core\BusinessLogic\AdyenAPI\Capture\Requests\CaptureHttpRequest;
use Adyen\Core\BusinessLogic\AdyenAPI\Http\Authorized\AuthorizedProxy;
use Adyen\Core\BusinessLogic\Domain\Capture\Models\CaptureRequest;
use Adyen\Core\BusinessLogic\Domain\Capture\Proxies\CaptureProxy;
use Adyen\Core\Infrastructure\Http\Exceptions\HttpRequestException;

/**
 * Class Proxy
 *
 * @package Adyen\Core\BusinessLogic\AdyenAPI\Capture\Http
 */
class Proxy extends AuthorizedProxy implements CaptureProxy
{
    /**
     * @inheritDoc
     *
     * @throws HttpRequestException
     */
    public function capturePayment(CaptureRequest $request): bool
    {
        $httpRequest = new CaptureHttpRequest($request);
        $response = $this->post($httpRequest)->decodeBodyToArray();

        return $response['status'] === 'received';
    }
}
