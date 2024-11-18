<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;

/**
 * Class UpdatePaymentDetailsResult
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models
 */
class UpdatePaymentDetailsResult
{
    /**
     * @var ResultCode
     */
    private $resultCode;
    /**
     * @var string|null
     */
    private $pspReference;
    /**
     * @var string
     */
    private $donationToken;
    /**
     * @var string
     */
    private $merchantReference;
    /**
     * @var string
     */
    private $paymentMethod;
    /**
     * @var Amount|null
     */
    private $amount;

    public function __construct(
        ResultCode $resultCode,
        ?string $pspReference = null,
        string $donationToken = '',
        string $merchantReference = '',
        string $paymentMethod = '',
        ?Amount $amount = null
    )
    {
        $this->resultCode = $resultCode;
        $this->pspReference = $pspReference;
        $this->donationToken = $donationToken;
        $this->merchantReference = $merchantReference;
        $this->paymentMethod = $paymentMethod;
    }

    /**
     * @return ResultCode
     */
    public function getResultCode(): ResultCode
    {
        return $this->resultCode;
    }

    public function getPspReference(): ?string
    {
        return $this->pspReference;
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
    public function getMerchantReference(): string
    {
        return $this->merchantReference;
    }

    /**
     * @return string
     */
    public function getPaymentMethod(): string
    {
        return $this->paymentMethod;
    }

    /**
     * @return Amount|null
     */
    public function getAmount(): ?Amount
    {
        return $this->amount;
    }
}
