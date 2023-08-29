<?php

namespace Adyen\Core\BusinessLogic\Domain\Payment\Models\MethodAdditionalData;

/**
 * Class IDEALonlineBankingThailand
 *
 * @package Adyen\Core\BusinessLogic\Domain\Payment\Models\MethodAdditionalData
 */
class IDEALonlineBankingThailand implements PaymentMethodAdditionalData
{
    /**
     * @var bool
     */
    private $showLogos;
    /**
     * @var string
     */
    private $bankIssuer;

    /**
     * @param bool $showLogos
     * @param string $bankIssuer
     */
    public function __construct(bool $showLogos = false, string $bankIssuer = '')
    {
        $this->showLogos = $showLogos;
        $this->bankIssuer = $bankIssuer;
    }

    /**
     * @return bool
     */
    public function isShowLogos(): bool
    {
        return $this->showLogos;
    }

    /**
     * @return string
     */
    public function getBankIssuer(): string
    {
        return $this->bankIssuer;
    }
}
