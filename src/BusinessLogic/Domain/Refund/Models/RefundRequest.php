<?php

namespace Adyen\Core\BusinessLogic\Domain\Refund\Models;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;

/**
 * Class RefundRequest
 *
 * @package Adyen\Core\BusinessLogic\Domain\Refund\Models
 */
class RefundRequest
{
    /**
     * @var string
     */
    private $pspReference;

    /**
     * @var Amount
     */
    private $amount;

    /**
     * @var string
     */
    private $merchantAccount;

    /**
     * @param string $pspReference
     * @param Amount $amount
     * @param string $merchantAccount
     */
    public function __construct(string $pspReference, Amount $amount, string $merchantAccount)
    {
        $this->pspReference = $pspReference;
        $this->amount = $amount;
        $this->merchantAccount = $merchantAccount;
    }

    /**
     * @return string
     */
    public function getPspReference(): string
    {
        return $this->pspReference;
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
    public function getMerchantAccount(): string
    {
        return $this->merchantAccount;
    }
}
