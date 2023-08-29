<?php

namespace Adyen\Core\Tests\BusinessLogic\CheckoutAPI\Donations\MockComponents;

use Adyen\Core\BusinessLogic\Domain\Checkout\AdyenGiving\Models\DonationRequest;
use Adyen\Core\BusinessLogic\Domain\Checkout\AdyenGiving\Proxies\DonationsProxy;
use Adyen\Core\Infrastructure\Http\Exceptions\HttpRequestException;

class MockDonationsProxy implements DonationsProxy
{
    /**
     * @var bool
     */
    public $isSuccessful = true;
    /**
     * @inheritDoc
     */
    public function makeDonation(DonationRequest $request): string
    {
        if (!$this->isSuccessful) {
            throw new HttpRequestException('Failed to make donation.');
        }

        return 'completed';
    }
}
