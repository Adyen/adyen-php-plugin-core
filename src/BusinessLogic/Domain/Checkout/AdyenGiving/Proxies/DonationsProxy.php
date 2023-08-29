<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\AdyenGiving\Proxies;

use Adyen\Core\BusinessLogic\Domain\Checkout\AdyenGiving\Models\DonationRequest;
use Exception;

/**
 * Class DonationsProxy
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\AdyenGiving\Proxies
 */
interface DonationsProxy
{
    /**
     * Makes donation request, returns status of the donation transaction.
     *
     * @param DonationRequest $request
     *
     * @return string
     *
     * @throws Exception
     */
    public function makeDonation(DonationRequest $request): string;
}
