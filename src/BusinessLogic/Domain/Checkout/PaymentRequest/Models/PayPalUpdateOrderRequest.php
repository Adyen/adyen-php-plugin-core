<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\InvalidCurrencyCode;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Currency;

/**
 * Class PayPalUpdateOrderRequest
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models
 */
class PayPalUpdateOrderRequest
{
    /**
     * @var Amount
     */
    private $amount;
    /**
     * @var string
     */
    private $paymentData;
    /**
     * @var string
     */
    private $pspReference;

    /**
     * @param Amount $amount
     * @param string $paymentData
     * @param string $pspReference
     */
    public function __construct(Amount $amount, string $paymentData, string $pspReference)
    {
        $this->amount = $amount;
        $this->paymentData = $paymentData;
        $this->pspReference = $pspReference;
    }

    public function getAmount(): Amount
    {
        return $this->amount;
    }

    public function getPaymentData(): string
    {
        return $this->paymentData;
    }

    public function getPspReference(): string
    {
        return $this->pspReference;
    }

    /**
     * @param array $rawRequest
     *
     * @return PayPalUpdateOrderRequest
     *
     * @throws InvalidCurrencyCode
     */
    public static function parse(array $rawRequest): PayPalUpdateOrderRequest
    {
        return new self(
            $rawRequest['amount'] ?: null,
            $rawRequest['paymentData'] ?: '',
            $rawRequest['pspReference'] ?: ''
        );
    }
}
