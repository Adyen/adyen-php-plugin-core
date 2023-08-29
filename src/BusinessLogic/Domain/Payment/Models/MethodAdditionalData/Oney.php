<?php

namespace Adyen\Core\BusinessLogic\Domain\Payment\Models\MethodAdditionalData;

/**
 * Class Oney
 *
 * @package Adyen\Core\BusinessLogic\Domain\Payment\Models\MethodAdditionalData
 */
class Oney implements PaymentMethodAdditionalData
{
    /**
     * @var string[]
     */
    private $supportedInstallments;

    /**
     * @param string[] $supportedInstallments
     */
    public function __construct(array $supportedInstallments = [])
    {
        $this->supportedInstallments = $supportedInstallments;
    }

    /**
     * @return string[]
     */
    public function getSupportedInstallments(): array
    {
        return $this->supportedInstallments;
    }
}
