<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\ApplicationInfo;

/**
 * Class MerchantApplication
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\ApplicationInfo
 */
class MerchantApplication
{
    /**
     * @var string
     */
    private $version;

    /**
     * @var string
     */
    private $name;

    /**
     * @param string $name
     * @param string|null $version
     */
    public function __construct(string $name, string $version = null)
    {
        $this->name = $name;
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getVersion(): ?string
    {
        return $this->version;
    }
}
