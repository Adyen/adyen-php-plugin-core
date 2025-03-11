<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models;

/**
 * Class Order
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models
 */
class Order
{
    /**
     * @var string
     */
    private $orderData;
    /**
     * @var string
     */
    private $pspReference;

    /**
     * @param string $orderData
     * @param string $pspReference
     */
    public function __construct(string $orderData, string $pspReference)
    {
        $this->orderData = $orderData;
        $this->pspReference = $pspReference;
    }

    public function getOrderData(): string
    {
        return $this->orderData;
    }

    public function getPspReference(): string
    {
        return $this->pspReference;
    }
}
