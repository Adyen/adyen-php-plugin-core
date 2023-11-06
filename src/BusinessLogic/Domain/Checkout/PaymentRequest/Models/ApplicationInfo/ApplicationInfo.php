<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\ApplicationInfo;

/**
 * Class ApplicationInfo
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\ApplicationInfo
 */
class ApplicationInfo
{
    /**
     * @var ExternalPlatform
     */
    private $externalPlatform;

    /**
     * @var MerchantApplication|null
     */
    private $merchantApplication;

    /**
     * @param ExternalPlatform|null $externalPlatform
     * @param MerchantApplication|null $merchantApplication
     */
    public function __construct(?ExternalPlatform $externalPlatform, ?MerchantApplication $merchantApplication)
    {
        $this->externalPlatform = $externalPlatform;
        $this->merchantApplication = $merchantApplication;
    }

    /**
     * @return ExternalPlatform|null
     */
    public function getExternalPlatform(): ?ExternalPlatform
    {
        return $this->externalPlatform;
    }

    /**
     * @return MerchantApplication|null
     */
    public function getMerchantApplication(): ?MerchantApplication
    {
        return $this->merchantApplication;
    }
}
