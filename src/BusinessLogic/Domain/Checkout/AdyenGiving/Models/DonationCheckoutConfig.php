<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\AdyenGiving\Models;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;

/**
 * Class DonationCheckoutConfig
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\AdyenGiving\Models
 */
class DonationCheckoutConfig
{
    /**
     * @var string
     */
    private $clientKey;
    /**
     * @var string
     */
    private $environment;
    /**
     * @var string
     */
    private $backgroundUrl;
    /**
     * @var string
     */
    private $description;
    /**
     * @var string
     */
    private $logoUrl;
    /**
     * @var string
     */
    private $name;
    /**
     * @var string
     */
    private $url;
    /**
     * @var string
     */
    private $currency;
    /**
     * @var Amount[]
     */
    private $amounts;

    /**
     * @param string $clientKey
     * @param string $environment
     * @param string $backgroundUrl
     * @param string $description
     * @param string $logoUrl
     * @param string $name
     * @param string $url
     * @param string $currency
     * @param Amount[] $amounts
     */
    public function __construct(
        string $clientKey,
        string $environment,
        string $backgroundUrl,
        string $description,
        string $logoUrl,
        string $name,
        string $url,
        string $currency,
        array  $amounts
    )
    {
        $this->clientKey = $clientKey;
        $this->environment = $environment;
        $this->backgroundUrl = $backgroundUrl;
        $this->description = $description;
        $this->logoUrl = $logoUrl;
        $this->name = $name;
        $this->url = $url;
        $this->currency = $currency;
        $this->amounts = $amounts;
    }

    /**
     * @return string
     */
    public function getClientKey(): string
    {
        return $this->clientKey;
    }

    /**
     * @return string
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }

    /**
     * @return string
     */
    public function getBackgroundUrl(): string
    {
        return $this->backgroundUrl;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getLogoUrl(): string
    {
        return $this->logoUrl;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @return Amount[]
     */
    public function getAmounts(): array
    {
        return $this->amounts;
    }
}
