<?php

namespace Adyen\Core\BusinessLogic\CheckoutAPI\PartialPayment\Request;

/**
 * Class BalanceCheckRequest
 *
 * @package Adyen\Core\BusinessLogic\CheckoutAPI\PartialPayment\Request
 */
class BalanceCheckRequest
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
     * @var array
     */
    private $paymentMethod;

    /**
     * @param float $amount
     * @param string $currency
     * @param array $paymentMethod
     */
    public function __construct(float $amount, string $currency, array $paymentMethod)
    {
        $this->amount = $amount;
        $this->currency = $currency;
        $this->paymentMethod = $paymentMethod;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getPaymentMethod(): array
    {
        return $this->paymentMethod;
    }
}
