<?php

namespace Adyen\Core\BusinessLogic\Domain\Cancel\Models;

/**
 * Class CancelRequest
 *
 * @package Adyen\Core\BusinessLogic\Domain\Cancel\Models
 */
class CancelRequest
{
    /**
     * @var string
     */
    private $merchantReference;

    /**
     * @var string
     */
    private $merchantAccount;
    /**
     * @var string
     */
    private $pspReference;

    /**
     * @param string $merchantReference
     * @param string $merchantAccount
     */
    public function __construct(string $pspReference, string $merchantReference, string $merchantAccount)
    {
        $this->pspReference = $pspReference;
        $this->merchantReference = $merchantReference;
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
     * @return string
     */
    public function getMerchantReference(): string
    {
        return $this->merchantReference;
    }

    /**
     * @return string
     */
    public function getMerchantAccount(): string
    {
        return $this->merchantAccount;
    }
}
