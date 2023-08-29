<?php

namespace Adyen\Core\BusinessLogic\Domain\Payment\Models\MethodAdditionalData;

/**
 * Class ApplePay
 *
 * @package Adyen\Core\BusinessLogic\Domain\Payment\Models\MethodAdditionalData
 */
class ApplePay implements PaymentMethodAdditionalData
{
    /**
     * @var string
     */
    private $merchantName;
    /**
     * @var string
     */
    private $merchantId;
    /**
     * @var bool
     */
    private $displayButtonOn;

    /**
     * @param string $merchantName
     * @param string $merchantId
     * @param bool $displayButtonOn
     */
    public function __construct(string $merchantName = '', string $merchantId = '', bool $displayButtonOn = false)
    {
        $this->merchantName = $merchantName;
        $this->merchantId = $merchantId;
        $this->displayButtonOn = $displayButtonOn;
    }

    /**
     * @return string
     */
    public function getMerchantName(): string
    {
        return $this->merchantName;
    }

    /**
     * @return string
     */
    public function getMerchantId(): string
    {
        return $this->merchantId;
    }

    /**
     * @return bool
     */
    public function getDisplayButtonOn(): bool
    {
        return $this->displayButtonOn;
    }
}
