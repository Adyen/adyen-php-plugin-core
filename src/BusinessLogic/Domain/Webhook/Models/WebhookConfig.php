<?php

namespace Adyen\Core\BusinessLogic\Domain\Webhook\Models;

/**
 * Class WebhookResponse
 *
 * @package Adyen\Core\BusinessLogic\Domain\Webhook\Models
 */
class WebhookConfig
{
    /**
     * @var string
     */
    private $id;
    /**
     * @var bool
     */
    private $active;
    /**
     * @var string
     */
    private $username;
    /**
     * @var string
     */
    private $password;
    /**
     * @var string
     */
    private $hmac;
    /**
     * @var string
     */
    private $merchantId;

    /**
     * @param string $id
     * @param string $merchantId
     * @param bool $active
     * @param string $username
     * @param string $password
     * @param string $hmac
     */
    public function __construct(string $id, string $merchantId, bool $active, string $username, string $password = '', string $hmac = '')
    {
        $this->id = $id;
        $this->active = $active;
        $this->username = $username;
        $this->password = $password;
        $this->hmac = $hmac;
        $this->merchantId = $merchantId;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getMerchantId(): string
    {
        return $this->merchantId;
    }

    /**
     * @param string $merchantId
     */
    public function setMerchantId(string $merchantId): void
    {
        $this->merchantId = $merchantId;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getHmac(): string
    {
        return $this->hmac;
    }

    /**
     * @param string $hmac
     */
    public function setHmac(string $hmac): void
    {
        $this->hmac = $hmac;
    }
}
