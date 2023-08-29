<?php

namespace Adyen\Core\BusinessLogic\Domain\Merchant\Models;

/**
 * Class Merchant
 *
 * @package Adyen\Core\BusinessLogic\Domain\Merchant\Models
 */
class Merchant
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
     * @var string
     */
    private $clientPrefix;
    /**
     * @var string
     */
    private $company;

    /**
     * @param string $merchantName
     * @param string $merchantId
     * @param string $clientPrefix
     * @param string $company
     */
    public function __construct(string $merchantId, string $merchantName, string $clientPrefix, string $company)
    {
        $this->merchantName = $merchantName;
        $this->merchantId = $merchantId;
        $this->clientPrefix = $clientPrefix;
        $this->company = $company;
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
     * @return string
     */
    public function getClientPrefix(): string
    {
        return $this->clientPrefix;
    }

    /**
     * @return string
     */
    public function getCompany(): string
    {
        return $this->company;
    }
}
