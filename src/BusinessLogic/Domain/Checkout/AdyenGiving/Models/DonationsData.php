<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\AdyenGiving\Models;

/**
 * Class DonationsData
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\AdyenGiving\Models
 */
class DonationsData
{
    /**
     * @var string
     */
    private $merchantReference;
    /**
     * @var string
     */
    private $donationToken;
    /**
     * @var string
     */
    private $pspReference;
    /**
     * @var string
     */
    private $paymentMethod;

    /**
     * @param string $merchantReference
     * @param string $donationToken
     * @param string $pspReference
     * @param string $paymentMethod
     */
    public function __construct(
        string $merchantReference,
        string $donationToken,
        string $pspReference,
        string $paymentMethod
    )
    {
        $this->merchantReference = $merchantReference;
        $this->donationToken = $donationToken;
        $this->pspReference = $pspReference;
        $this->paymentMethod = $paymentMethod;
    }

    /**
     * @return string
     */
    public function getMerchantReference(): string
    {
        return $this->merchantReference;
    }

    /**
     * @return string
     */
    public function getDonationToken(): string
    {
        return $this->donationToken;
    }

    /**
     * @return string
     */
    public function getPspReference(): string
    {
        return $this->pspReference;
    }

    /**
     * @return string
     */
    public function getPaymentMethod(): string
    {
        return $this->paymentMethod;
    }
}
