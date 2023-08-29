<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\AdyenGiving\Models;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;

/**
 * Class DonationRequest
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\AdyenGiving\Models
 */
class DonationRequest
{
    /**
     * @var string
     */
    private $donationToken;
    /**
     * @var Amount
     */
    private $amount;
    /**
     * @var string
     */
    private $paymentMethodType;
    /**
     * @var string
     */
    private $donationOriginalPspReference;
    /**
     * @var string
     */
    private $donationAccount;
    /**
     * @var string
     */
    private $merchantAccount;
    /**
     * @var string
     */
    private $returnUrl;

    /**
     * @param string $donationToken
     * @param Amount $amount
     * @param string $paymentMethodType
     * @param string $donationOriginalPspReference
     * @param string $donationAccount
     * @param string $merchantAccount
     * @param string $returnUrl
     */
    public function __construct(
        string $donationToken,
        Amount $amount,
        string $paymentMethodType,
        string $donationOriginalPspReference,
        string $donationAccount,
        string $merchantAccount,
        string $returnUrl
    )
    {
        $this->donationToken = $donationToken;
        $this->amount = $amount;
        $this->paymentMethodType = $paymentMethodType;
        $this->donationOriginalPspReference = $donationOriginalPspReference;
        $this->donationAccount = $donationAccount;
        $this->merchantAccount = $merchantAccount;
        $this->returnUrl = $returnUrl;
    }

    /**
     * @return string
     */
    public function getDonationToken(): string
    {
        return $this->donationToken;
    }

    /**
     * @return Amount
     */
    public function getAmount(): Amount
    {
        return $this->amount;
    }

    /**
     * @return string
     */
    public function getPaymentMethodType(): string
    {
        return $this->paymentMethodType;
    }

    /**
     * @return string
     */
    public function getDonationOriginalPspReference(): string
    {
        return $this->donationOriginalPspReference;
    }

    /**
     * @return string
     */
    public function getDonationAccount(): string
    {
        return $this->donationAccount;
    }

    /**
     * @return string
     */
    public function getMerchantAccount(): string
    {
        return $this->merchantAccount;
    }

    /**
     * @return string
     */
    public function getReturnUrl(): string
    {
        return $this->returnUrl;
    }
}
