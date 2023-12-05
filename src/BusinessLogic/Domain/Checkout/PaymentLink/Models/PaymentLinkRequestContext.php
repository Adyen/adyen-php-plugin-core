<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Models;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use DateTime;

/**
 * Class PaymentLinkRequestContext.
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Models
 */
class PaymentLinkRequestContext
{
    /**
     * @var Amount
     */
    private $amount;

    /**
     * @var string
     */
    private $reference;

    /**
     * @var DateTime
     */
    private $expiresAt;

    /**
     * @param Amount $amount
     * @param string $reference
     * @param DateTime|null $expiresAt
     */
    public function __construct(Amount $amount, string $reference, DateTime $expiresAt = null)
    {
        $this->amount = $amount;
        $this->reference = $reference;
        $this->expiresAt = $expiresAt;
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
    public function getReference(): string
    {
        return $this->reference;
    }

    /**
     * @return DateTime|null
     */
    public function getExpiresAt(): ?DateTime
    {
        return $this->expiresAt;
    }
}
