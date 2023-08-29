<?php

namespace Adyen\Core\BusinessLogic\Domain\Payment\Models\MethodAdditionalData;

/**
 * Class GooglePay
 *
 * @package Adyen\Core\BusinessLogic\Domain\Payment\Models\MethodAdditionalData
 */
class GooglePay implements PaymentMethodAdditionalData
{
    /**
     * @var string
     */
    private $gatewayMerchantId;
    /**
     * @var string
     */
    private $merchantId;
    /**
     * @var bool
     */
    private $displayButtonOn;

    /**
     * @param string $gatewayMerchantId
     * @param string $merchantId
     * @param bool $displayButtonOn
     */
    public function __construct(string $gatewayMerchantId = '', string $merchantId = '', bool $displayButtonOn = false)
    {
        $this->gatewayMerchantId = $gatewayMerchantId;
        $this->merchantId = $merchantId;
        $this->displayButtonOn = $displayButtonOn;
    }

    /**
     * @return string
     */
    public function getGatewayMerchantId(): string
    {
        return $this->gatewayMerchantId;
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
