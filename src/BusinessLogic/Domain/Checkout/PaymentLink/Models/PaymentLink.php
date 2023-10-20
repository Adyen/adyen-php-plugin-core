<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Models;

/**
 * Class PaymentLinkResponse.
 *
 * @package Adyen\Core\BusinessLogic\Domain\PaymentLink\Models
 */
class PaymentLink
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $expiresAt;

    /**
     * @param string $url
     * @param string $expiresAt
     */
    public function __construct(string $url, string $expiresAt)
    {
        $this->url = $url;
        $this->expiresAt = $expiresAt;
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
    public function getExpiresAt(): string
    {
        return $this->expiresAt;
    }
}
