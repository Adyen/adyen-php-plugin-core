<?php

namespace Adyen\Core\BusinessLogic\AdyenAPI\AuthorizationAdjustment\Http;

use Adyen\Core\BusinessLogic\AdyenAPI\AuthorizationAdjustment\Requests\AuthorizationAdjustmentHttpRequest;
use Adyen\Core\BusinessLogic\AdyenAPI\Http\Authorized\AuthorizedProxy;
use Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Models\AuthorizationAdjustmentRequest;
use Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Proxies\AuthorizationAdjustmentProxy;
use Adyen\Core\Infrastructure\Http\Exceptions\HttpRequestException;

/**
 * Class Proxy.
 *
 * @package Adyen\Core\BusinessLogic\AdyenAPI\AuthorizationAdjustment\Http
 */
class Proxy extends AuthorizedProxy implements AuthorizationAdjustmentProxy
{
    /**
     * @inheritDoc
     *
     * @throws HttpRequestException
     */
    public function adjustPayment(AuthorizationAdjustmentRequest $request): bool
    {
        $httpRequest = new AuthorizationAdjustmentHttpRequest($request);
        $response = $this->post($httpRequest)->decodeBodyToArray();

        return $response['status'] === 'received';
    }
}
