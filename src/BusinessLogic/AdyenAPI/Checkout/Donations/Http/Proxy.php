<?php

namespace Adyen\Core\BusinessLogic\AdyenAPI\Checkout\Donations\Http;

use Adyen\Core\BusinessLogic\AdyenAPI\Checkout\Donations\Requests\DonationHttpRequest;
use Adyen\Core\BusinessLogic\AdyenAPI\Http\Authorized\AuthorizedProxy;
use Adyen\Core\BusinessLogic\Domain\Checkout\AdyenGiving\Models\DonationRequest;
use Adyen\Core\BusinessLogic\Domain\Checkout\AdyenGiving\Proxies\DonationsProxy;

/**
 * Class Proxy
 *
 * @package Adyen\Core\BusinessLogic\AdyenAPI\Checkout\Donations\Http
 */
class Proxy extends AuthorizedProxy implements DonationsProxy
{
    /**
     * @inheritDoc
     */
    public function makeDonation(DonationRequest $request): string
    {
        $httpRequest = new DonationHttpRequest($request);
        $response = $this->post($httpRequest)->decodeBodyToArray();

        return $response['status'] ?? '';
    }
}
