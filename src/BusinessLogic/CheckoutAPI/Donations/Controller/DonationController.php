<?php

namespace Adyen\Core\BusinessLogic\CheckoutAPI\Donations\Controller;

use Adyen\Core\BusinessLogic\CheckoutAPI\Donations\Request\DonationSettingsRequest;
use Adyen\Core\BusinessLogic\CheckoutAPI\Donations\Request\MakeDonationRequest;
use Adyen\Core\BusinessLogic\CheckoutAPI\Donations\Response\DonationSettingsResponse;
use Adyen\Core\BusinessLogic\CheckoutAPI\Donations\Response\MakeDonationResponse;
use Adyen\Core\BusinessLogic\Domain\Checkout\AdyenGiving\Services\DonationsService;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\InvalidCurrencyCode;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\MissingClientKeyConfiguration;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Currency;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFountException;

/**
 * Class DonationController
 *
 * @package Adyen\Core\BusinessLogic\CheckoutAPI\Donations\Controller
 */
class DonationController
{
    /**
     * @var DonationsService
     */
    private $service;

    /**
     * @param  DonationsService  $service
     */
    public function __construct(DonationsService $service)
    {
        $this->service = $service;
    }

    /**
     * @param  MakeDonationRequest  $request
     *
     * @return MakeDonationResponse
     *
     * @throws InvalidCurrencyCode
     * @throws ConnectionSettingsNotFountException
     */
    public function makeDonation(MakeDonationRequest $request): MakeDonationResponse
    {
        return new MakeDonationResponse($this->service->makeDonation(
            Amount::fromInt($request->getAmount(), Currency::fromIsoCode($request->getCurrency())),
            $request->getMerchantReference()
        )
        );
    }

    /**
     * @param  string  $merchantReference
     * @param  string  $currencyFactor
     *
     * @return DonationSettingsResponse
     *
     * @throws ConnectionSettingsNotFountException
     */
    public function getDonationSettings(string $merchantReference, string $currencyFactor): DonationSettingsResponse
    {
        return new DonationSettingsResponse(
            $this->service->getDonationSettings($merchantReference, $currencyFactor)
        );
    }
}
