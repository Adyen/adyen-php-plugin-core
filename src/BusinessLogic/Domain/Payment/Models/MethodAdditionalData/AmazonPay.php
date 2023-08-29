<?php

namespace Adyen\Core\BusinessLogic\Domain\Payment\Models\MethodAdditionalData;

/**
 * Class AmazonPay
 *
 * @package Adyen\Core\BusinessLogic\Domain\Payment\Models\MethodAdditionalData
 */
class AmazonPay implements PaymentMethodAdditionalData
{
    /**
     * @var string
     */
    private $publicKeyId;
    /**
     * @var string
     */
    private $merchantId;
    /**
     * @var string
     */
    private $storeId;
    /**
     * @var bool
     */
    private $displayButtonOn;

    /**
     * @param string $publicKeyId
     * @param string $merchantId
     * @param string $storeId
     * @param bool $displayButtonOn
     */
    public function __construct(
        string $publicKeyId = '',
        string $merchantId = '',
        string $storeId = '',
        bool $displayButtonOn = false
    )
    {
        $this->publicKeyId = $publicKeyId;
        $this->merchantId = $merchantId;
        $this->storeId = $storeId;
        $this->displayButtonOn = $displayButtonOn;
    }

    /**
     * @return string
     */
    public function getPublicKeyId(): string
    {
        return $this->publicKeyId;
    }

    /**
     * @return string
     */
    public function getMerchantId(): string
    {
        return $this->merchantId;
    }

    /**
     * @return string
     */
    public function getStoreId(): string
    {
        return $this->storeId;
    }

    /**
     * @return bool
     */
    public function getDisplayButtonOn(): bool
    {
        return $this->displayButtonOn;
    }
}
