<?php

namespace Adyen\Core\BusinessLogic\DataAccess\Webhook\Entities;

use Adyen\Core\BusinessLogic\Domain\Webhook\Models\WebhookConfig as WebhookConfigModel;
use Adyen\Core\Infrastructure\ORM\Configuration\EntityConfiguration;
use Adyen\Core\Infrastructure\ORM\Configuration\IndexMap;
use Adyen\Core\Infrastructure\ORM\Entity;

/**
 * Class WebhookConfig
 *
 * @package Adyen\Core\BusinessLogic\DataAccess\Webhook\Entities
 */
class WebhookConfig extends Entity
{
    /**
     * Fully qualified name of this class.
     */
    public const CLASS_NAME = __CLASS__;
    /**
     * @var string
     */
    protected $storeId;
    /**
     * @var WebhookConfigModel
     */
    protected $webhookConfig;
    /**
     * Array of field names.
     *
     * @var array
     */
    protected $fields = ['id', 'storeId'];

    /**
     * @inheritDoc
     */
    public function getConfig(): EntityConfiguration
    {
        $indexMap = new IndexMap();

        $indexMap->addStringIndex('storeId');

        return new EntityConfiguration($indexMap, 'WebhookConfig');
    }

    /**
     * @inheritDoc
     */
    public function inflate(array $data): void
    {
        parent::inflate($data);
        $webhookConfig = $data['webhookConfig'] ?? [];

        $this->webhookConfig = new WebhookConfigModel(
            static::getDataValue($webhookConfig, 'id'),
            static::getDataValue($webhookConfig, 'merchantId'),
            static::getDataValue($webhookConfig, 'active'),
            static::getDataValue($webhookConfig, 'username'),
            static::getDataValue($webhookConfig, 'password'),
            static::getDataValue($webhookConfig, 'hmac')
        );
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $data = parent::toArray();
        $data['webhookConfig'] = [
            'id' => $this->webhookConfig->getId(),
            'merchantId' => $this->webhookConfig->getMerchantId(),
            'active' => $this->webhookConfig->isActive(),
            'username' => $this->webhookConfig->getUsername(),
            'password' => $this->webhookConfig->getPassword(),
            'hmac' => $this->webhookConfig->getHmac(),
        ];

        return $data;
    }

    /**
     * @return string
     */
    public function getStoreId(): string
    {
        return $this->storeId;
    }

    /**
     * @param string $storeId
     */
    public function setStoreId(string $storeId): void
    {
        $this->storeId = $storeId;
    }

    /**
     * @return WebhookConfigModel
     */
    public function getWebhookConfig(): WebhookConfigModel
    {
        return $this->webhookConfig;
    }

    /**
     * @param WebhookConfigModel $webhookConfig
     */
    public function setWebhookConfig(WebhookConfigModel $webhookConfig): void
    {
        $this->webhookConfig = $webhookConfig;
    }
}
