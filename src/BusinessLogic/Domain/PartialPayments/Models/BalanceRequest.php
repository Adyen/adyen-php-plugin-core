<?php

namespace Adyen\Core\BusinessLogic\Domain\PartialPayments\Models;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;

/**
 * Class BalanceRequest
 *
 * @package Adyen\Core\BusinessLogic\Domain\PartialPayments\Models
 */
class BalanceRequest
{
    /**
     * @var array
     */
    private $paymentMethod;
    /**
     * @var string
     */
    private $merchantAccount;
    /**
     * @var Amount
     */
    private $amount;

    /**
     * @param array $paymentMethod
     * @param string $merchantAccount
     * @param Amount $amount
     */
    public function __construct(array $paymentMethod, string $merchantAccount, Amount $amount)
    {
        $this->paymentMethod = $paymentMethod;
        $this->merchantAccount = $merchantAccount;
        $this->amount = $amount;
    }

    public function getPaymentMethod(): array
    {
        return $this->paymentMethod;
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
