<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models;

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

    public function __construct(
        ResultCode $resultCode,
        ?string $pspReference = null,
        string $donationToken = '',
        string $merchantReference = '',
        string $paymentMethod = ''
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
}
