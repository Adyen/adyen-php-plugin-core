<?php

namespace Adyen\Core\BusinessLogic\AdyenAPI\Checkout\Donations\Requests;

use Adyen\Core\BusinessLogic\AdyenAPI\Http\Requests\HttpRequest;
use Adyen\Core\BusinessLogic\Domain\Checkout\AdyenGiving\Models\DonationRequest;

/**
 * Class DonationHttpRequest
 *
 * @package Adyen\Core\BusinessLogic\AdyenAPI\Checkout\Donations\Requests
 */
class DonationHttpRequest extends HttpRequest
{
    private const SHOPPER_INTERACTION = 'ContAuth';
    /**
     * @var DonationRequest
     */
    private $donationRequest;

    /**
     * @param DonationRequest $donationRequest
     */
    public function __construct(DonationRequest $donationRequest)
    {
        $this->donationRequest = $donationRequest;

        parent::__construct('/donations', $this->transformBody());
    }

    /**
     * @return array
     */
    private function transformBody(): array
    {
        return [
            'donationToken' => $this->donationRequest->getDonationToken(),
            'amount' => [
                'currency' => $this->donationRequest->getAmount()->getCurrency()->getIsoCode(),
                'value' => $this->donationRequest->getAmount()->getValue(),
            ],
            'paymentMethod' => [
                'type' => $this->donationRequest->getPaymentMethodType(),
            ],
            'donationOriginalPspReference' => $this->donationRequest->getDonationOriginalPspReference(),
            'reference' => $this->donationRequest->getDonationOriginalPspReference(),
            'donationAccount' => $this->donationRequest->getDonationAccount(),
            'shopperInteraction' => self::SHOPPER_INTERACTION,
            'merchantAccount' => $this->donationRequest->getMerchantAccount(),
            'returnUrl' => $this->donationRequest->getReturnUrl(),
        ];
    }
}
