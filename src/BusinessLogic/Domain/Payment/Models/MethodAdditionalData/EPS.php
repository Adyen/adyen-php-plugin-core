<?php

namespace Adyen\Core\BusinessLogic\Domain\Payment\Models\MethodAdditionalData;

/**
 * Class EPS
 *
 * @package Adyen\Core\BusinessLogic\Domain\Payment\Models\MethodAdditionalData
 */
class EPS implements PaymentMethodAdditionalData
{
    /**
     * @var string
     */
    private $bankIssuer;

    /**
     * @param string $bankIssuer
     */
    public function __construct(string $bankIssuer = '')
    {
        $this->bankIssuer = $bankIssuer;
    }

    /**
     * @return string
     */
    public function getBankIssuer(): string
    {
        return $this->bankIssuer;
    }
}
