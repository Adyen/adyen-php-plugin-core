<?php

namespace Adyen\Core\BusinessLogic\Domain\PartialPayments\Models;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;

/**
 * Class OrderCreateResult
 *
 * @package Adyen\Core\BusinessLogic\Domain\PartialPayments\Models
 */
class OrderCreateResult
{
    /**
     * @var Amount
     */
    private $amount;
    /**
     * @var string
     */
    private $expiresAt;
    /**
     * @var string
     */
    private $orderData;
    /**
     * @var string
     */
    private $pspReference;
    /**
     * The reference provided by merchant for creating the order.
     *
     * @var string
     */
    private $reference;
    /**
     * @var Amount
     */
    private $remainingAmount;

    /**
     * @param Amount $amount
     * @param string $expiresAt
     * @param string $orderData
     * @param string $pspReference
     * @param string $reference
     * @param Amount $remainingAmount
     */
    public function __construct(
        Amount $amount,
        string $expiresAt,
        string $orderData,
        string $pspReference,
        string $reference,
        Amount $remainingAmount
    )
    {
        $this->amount = $amount;
        $this->expiresAt = $expiresAt;
        $this->orderData = $orderData;
        $this->pspReference = $pspReference;
        $this->reference = $reference;
        $this->remainingAmount = $remainingAmount;
    }

    public function getAmount(): Amount
    {
        return $this->amount;
    }

    public function getExpiresAt(): string
    {
        return $this->expiresAt;
    }

    public function getOrderData(): string
    {
        return $this->orderData;
    }

    public function getPspReference(): string
    {
        return $this->pspReference;
    }

    public function getReference(): string
    {
        return $this->reference;
    }

    public function getRemainingAmount(): Amount
    {
        return $this->remainingAmount;
    }
}
