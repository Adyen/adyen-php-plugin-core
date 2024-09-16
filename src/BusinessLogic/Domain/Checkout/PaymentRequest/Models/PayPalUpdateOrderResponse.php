<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models;

/**
 * Class PayPalUpdateOrderResponse
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models
 */
class PayPalUpdateOrderResponse
{
    /**
     * @var string
     */
    private $paymentData;
    /**
     * @var string
     */
    private $status;

    /**
     * @param string $paymentData
     * @param string $status
     */
    public function __construct(string $paymentData, string $status)
    {
        $this->paymentData = $paymentData;
        $this->status = $status;
    }

    public function getPaymentData(): string
    {
        return $this->paymentData;
    }

    public function getStatus(): string
    {
        return $this->status;
    }
}
