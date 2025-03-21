<?php

namespace Adyen\Core\BusinessLogic\Domain\PartialPayments\Models;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;

class BalanceResult
{
    /**
     * @var string
     */
    private $pspReference;
    /**
     * Possible values: Success, NotEnoughBalance, Failed
     *
     * @var string
     */
    private $resultCode;
    /**
     * @var Amount
     */
    private $balance;

    /**
     * @param string $pspReference
     * @param string $resultCode
     * @param Amount|null $balance
     */
    public function __construct(string $pspReference, string $resultCode, ?Amount $balance)
    {
        $this->pspReference = $pspReference;
        $this->resultCode = $resultCode;
        $this->balance = $balance;
    }

    public function getPspReference(): string
    {
        return $this->pspReference;
    }

    public function getResultCode(): string
    {
        return $this->resultCode;
    }

    public function getBalance(): ?Amount
    {
        return $this->balance;
    }
}