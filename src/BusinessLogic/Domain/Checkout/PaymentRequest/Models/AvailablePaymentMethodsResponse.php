<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models;

/**
 * Class AvailablePaymentMethodsResponse
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models
 */
class AvailablePaymentMethodsResponse
{
    /**
     * @var PaymentMethodResponse[]
     */
    private $paymentMethodsResponse;
    /**
     * @var PaymentMethodResponse[]
     */
    private $storedPaymentMethodsResponse;

    /**
     * AvailablePaymentMethodsResponse constructor.
     *
     * @param PaymentMethodResponse[] $paymentMethodsResponse Available payment methods to show on the checkout
     * @param PaymentMethodResponse[] $storedPaymentMethodsResponse Available stored payment methods to show on the checkout
     */
    public function __construct(
        array $paymentMethodsResponse = [],
        array $storedPaymentMethodsResponse = []
    ) {
        $this->paymentMethodsResponse = $paymentMethodsResponse;
        $this->storedPaymentMethodsResponse = $storedPaymentMethodsResponse;
    }

    /**
     * @return PaymentMethodResponse[]
     */
    public function getPaymentMethodsResponse(): array
    {
        return $this->paymentMethodsResponse;
    }

    /**
     * @return PaymentMethodResponse[]
     */
    public function getStoredPaymentMethodsResponse(): array
    {
        return $this->storedPaymentMethodsResponse;
    }
}
