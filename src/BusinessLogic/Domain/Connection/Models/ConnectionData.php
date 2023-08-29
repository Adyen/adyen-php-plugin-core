<?php

namespace Adyen\Core\BusinessLogic\Domain\Connection\Models;

/**
 * Class ConnectionData
 *
 * @package Adyen\Core\BusinessLogic\Domain\Connection\Models
 */
class ConnectionData
{
    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var string
     */
    protected $merchantId;
    /**
     * @var string
     */
    protected $clientPrefix;
    /**
     * @var string
     */
    protected $clientKey;
    /**
     * @var ApiCredentials
     */
    protected $apiCredentials;

    /**
     * @param string $apiKey
     * @param string $merchantId
     * @param string $clientPrefix
     * @param string $clientKey
     * @param ApiCredentials|null $apiCredentials
     */
    public function __construct(
        string $apiKey,
        string $merchantId,
        string $clientPrefix = '',
        string $clientKey = '',
        ApiCredentials $apiCredentials = null
    )
    {
        $this->apiKey = $apiKey;
        $this->merchantId = $merchantId;
        $this->clientPrefix = $clientPrefix;
        $this->clientKey = $clientKey;
        $this->apiCredentials = $apiCredentials;
    }

    /**
     * @return string
     */
    public function getApiKey(): string
    {
        return $this->apiKey;
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
     * @param string $clientPrefix
     */
    public function setClientPrefix(string $clientPrefix): void
    {
        $this->clientPrefix = $clientPrefix;
    }

    /**
     * @return ApiCredentials|null
     */
    public function getApiCredentials(): ?ApiCredentials
    {
        return $this->apiCredentials;
    }

    /**
     * @param ApiCredentials|null $apiCredentials
     */
    public function setApiCredentials(?ApiCredentials $apiCredentials): void
    {
        $this->apiCredentials = $apiCredentials;
    }

    /**
     * @return string
     */
    public function getClientKey(): string
    {
        return $this->clientKey;
    }

    /**
     * @param string $clientKey
     */
    public function setClientKey(string $clientKey): void
    {
        $this->clientKey = $clientKey;
    }
}
