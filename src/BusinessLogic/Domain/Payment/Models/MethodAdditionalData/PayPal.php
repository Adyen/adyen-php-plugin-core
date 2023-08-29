<?php

namespace Adyen\Core\BusinessLogic\Domain\Payment\Models\MethodAdditionalData;

/**
 * Class PayPal
 *
 * @package Adyen\Core\BusinessLogic\Domain\Payment\Models\MethodAdditionalData
 */
class PayPal implements PaymentMethodAdditionalData
{
    /**
     * @var bool
     */
    private $displayButtonOn;

    /**
     * @param bool $displayButtonOn
     */
    public function __construct(bool $displayButtonOn)
    {
        $this->displayButtonOn = $displayButtonOn;
    }

    /**
     * @return bool
     */
    public function getDisplayButtonOn(): bool
    {
        return $this->displayButtonOn;
    }
}
