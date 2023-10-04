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
     * @param string|null $merchantApplicationVersion
     */
    public function __construct(?ExternalPlatform $externalPlatform, ?string $merchantApplicationVersion)
    {
        $this->externalPlatform = $externalPlatform;
        $externalPlatform && $this->merchantApplication = new MerchantApplication(
            'Adyen ' . $externalPlatform->getName(),
            $merchantApplicationVersion
        );
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
