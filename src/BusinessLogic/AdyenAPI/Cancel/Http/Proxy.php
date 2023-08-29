<?php

namespace Adyen\Core\BusinessLogic\AdyenAPI\Cancel\Http;

use Adyen\Core\BusinessLogic\AdyenAPI\Cancel\Request\CancelHttpRequest;
use Adyen\Core\BusinessLogic\Domain\Cancel\Models\CancelRequest;
use Adyen\Core\BusinessLogic\AdyenAPI\Http\Authorized\AuthorizedProxy;
use Adyen\Core\BusinessLogic\Domain\Cancel\Proxies\CancelProxy;
use Adyen\Core\Infrastructure\Http\Exceptions\HttpRequestException;

/**
 * Class Proxy
 *
 * @package Adyen\Core\BusinessLogic\AdyenAPI\Cancel\Http
 */
class Proxy extends AuthorizedProxy implements CancelProxy
{
    /**
     * @inheritDoc
     *
     * @throws HttpRequestException
     */
    public function cancelPayment(CancelRequest $request): bool
    {
        $httpRequest = new CancelHttpRequest($request);
        $response = $this->post($httpRequest)->decodeBodyToArray();

        return $response['status'] === 'received';
    }
}
