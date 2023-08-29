<?php

namespace Adyen\Core\BusinessLogic\CheckoutAPI\Donations\Request;

/**
 * Class MakeDonationRequest
 *
 * @package Adyen\Core\BusinessLogic\CheckoutAPI\Donations\Request
 */
class MakeDonationRequest
{
    /**
     * @var float
     */
    private $amount;
    /**
     * @var string
     */
    private $currency;
    /**
     * @var string
     */
    private $merchantReference;

    /**
     * @param float $amount
     * @param string $currency
     * @param string $merchantReference
     */
    public function __construct(float $amount, string $currency, string $merchantReference)
    {
        $this->amount = $amount;
        $this->currency = $currency;
        $this->merchantReference = $merchantReference;
    }

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @return string
     */
    public function getMerchantReference(): string
    {
        return $this->merchantReference;
    }
}
