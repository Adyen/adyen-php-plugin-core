<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Models;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;

/**
 * Class PaymentLinkRequestContext.
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Models
 */
class StartPaymentLinkRequestContext
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
     * @var string
     */
    private $returnUrl;

    /**
     * @param Amount $amount
     * @param string $reference
     * @param string $returnUrl
     */
    public function __construct(Amount $amount, string $reference, string $returnUrl)
    {
        $this->amount = $amount;
        $this->reference = $reference;
        $this->returnUrl = $returnUrl;
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
     * @return string
     */
    public function getReturnUrl(): string
    {
        return $this->returnUrl;
    }
}
