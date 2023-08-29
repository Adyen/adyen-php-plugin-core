<?php

namespace Adyen\Core\BusinessLogic\AdyenAPI\Refund\Http;

use Adyen\Core\BusinessLogic\AdyenAPI\Refund\Requests\RefundHttpRequest;
use Adyen\Core\BusinessLogic\Domain\Refund\Models\RefundRequest;
use Adyen\Core\BusinessLogic\AdyenAPI\Http\Authorized\AuthorizedProxy;
use Adyen\Core\BusinessLogic\Domain\Refund\Proxies\RefundProxy;
use Adyen\Core\Infrastructure\Http\Exceptions\HttpRequestException;

/**
 * Class Proxy
 *
 * @package Adyen\Core\BusinessLogic\AdyenAPI\Refund\Http
 */
class Proxy extends AuthorizedProxy implements RefundProxy
{
    /**
     * @inheritDoc
     *
     * @throws HttpRequestException
     */
    public function refundPayment(RefundRequest $request): bool
    {
        $httpRequest = new RefundHttpRequest($request);
        $response = $this->post($httpRequest)->decodeBodyToArray();

        return $response['status'] === 'received';
    }
}
