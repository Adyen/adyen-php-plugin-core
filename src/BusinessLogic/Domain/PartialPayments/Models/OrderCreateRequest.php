<?php

namespace Adyen\Core\BusinessLogic\Domain\PartialPayments\Models;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;

/**
 * Class OrderCreateRequest
 *
 * @package Adyen\Core\BusinessLogic\Domain\PartialPayments\Models
 */
class OrderCreateRequest
{
    /**
     * @var string
     */
    private $reference;
    /**
     * @var string
     */
    private $merchantAccount;
    /**
     * @var Amount
     */
    private $amount;

    /**
     * @param string $reference
     * @param string $merchantAccount
     * @param Amount $amount
     */
    public function __construct(string $reference, string $merchantAccount, Amount $amount)
    {
        $this->reference = $reference;
        $this->merchantAccount = $merchantAccount;
        $this->amount = $amount;
    }

    public function getReference(): string
    {
        return $this->reference;
    }

    public function getMerchantAccount(): string
    {
        return $this->merchantAccount;
    }

    public function getAmount(): Amount
    {
        return $this->amount;
    }
}
