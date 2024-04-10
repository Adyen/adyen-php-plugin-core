<?php

namespace Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Models;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;

/**
 * Class AuthorizationAdjustmentRequest.
 *
 * @package Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Models
 */
class AuthorizationAdjustmentRequest
{
    /**
     *  Default reason constant.
     */
    private const DEFAULT_REASON = 'delayedCharge';

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
     * @var string
     */
    private $merchantReference;

    /**
     * @param string $pspReference
     * @param Amount $amount
     * @param string $merchantAccount
     * @param string $merchantReference
     */
    public function __construct(
        string $pspReference,
        Amount $amount,
        string $merchantAccount,
        string $merchantReference
    ) {
        $this->pspReference = $pspReference;
        $this->amount = $amount;
        $this->merchantAccount = $merchantAccount;
        $this->merchantReference = $merchantReference;
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

    /**
     * @return string
     */
    public function getMerchantReference(): string
    {
        return $this->merchantReference;
    }

    /**
     * @return string
     */
    public function getReason(): string
    {
        return self::DEFAULT_REASON;
    }
}
