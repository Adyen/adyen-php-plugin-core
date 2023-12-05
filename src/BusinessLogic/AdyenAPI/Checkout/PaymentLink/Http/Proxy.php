<?php

namespace Adyen\Core\BusinessLogic\AdyenAPI\Checkout\PaymentLink\Http;

use Adyen\Core\BusinessLogic\AdyenAPI\Checkout\PaymentLink\Requests\PaymentLinkHttpRequest;
use Adyen\Core\BusinessLogic\AdyenAPI\Http\Authorized\AuthorizedProxy;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Models\PaymentLink;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Models\PaymentLinkRequest;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Proxies\PaymentLinkProxy;
use Adyen\Core\Infrastructure\Http\Exceptions\HttpRequestException;

/**
 * Class Proxy
 *
 * @package Adyen\Core\BusinessLogic\AdyenAPI\Checkout\PaymentLink\Http
 */
class Proxy extends AuthorizedProxy implements PaymentLinkProxy
{
    /**
     * @param PaymentLinkRequest $request
     *
     * @return PaymentLink
     *
     * @throws HttpRequestException
     */
    public function createPaymentLink(PaymentLinkRequest $request): PaymentLink
    {
        $response = $this->post(new PaymentLinkHttpRequest($request))->decodeBodyToArray();

        return new PaymentLink($response['url'] ?? '', $response['expiresAt'] ?? '');
    }
}
