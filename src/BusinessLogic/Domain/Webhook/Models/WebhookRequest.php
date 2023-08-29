<?php

namespace Adyen\Core\BusinessLogic\Domain\Webhook\Models;

/**
 * Class WebhookRequest
 *
 * @package Adyen\Core\BusinessLogic\Domain\Webhook\Models
 */
class WebhookRequest
{
    /**
     * @var string
     */
    private $url;
    /**
     * @var string
     */
    private $username;
    /**
     * @var string
     */
    private $password;

    /**
     * @param string $url
     * @param string $username
     * @param string $password
     */
    public function __construct(
        string $url,
        string $username,
        string $password
    )
    {
        $this->url = $url;
        $this->username = $username;
        $this->password = $password;
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
}
