<?php

namespace Adyen\Core\BusinessLogic\CheckoutAPI\CheckoutConfig\Request;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Country;

/**
 * Class PaymentCheckoutConfigRequest
 *
 * @package Adyen\Core\BusinessLogic\CheckoutAPI\CheckoutConfig\Request
 */
class PaymentCheckoutConfigRequest
{
    /**
     * @var Amount
     */
    private $amount;
    /**
     * @var Country|null
     */
    private $country;
    /**
     * @var string
     */
    private $shopperLocale;
    /**
     * @var string|null
     */
    private $shopperReference;

    public function __construct(Amount $amount, Country $country = null, string $shopperLocale = 'en-US', string $shopperReference = null)
    {

        $this->amount = $amount;
        $this->country = $country;
        $this->shopperLocale = $shopperLocale;
        $this->shopperReference = $shopperReference;
    }

    /**
     * @return Amount
     */
    public function getAmount(): Amount
    {
        return $this->amount;
    }

    /**
     * @return ?Country
     */
    public function getCountry(): ?Country
    {
        return $this->country;
    }

    /**
     * @return string
     */
    public function getShopperLocale(): string
    {
        return $this->shopperLocale;
    }

    /**
     * @return string|null
     */
    public function getShopperReference(): ?string
    {
        return $this->shopperReference;
    }
}
