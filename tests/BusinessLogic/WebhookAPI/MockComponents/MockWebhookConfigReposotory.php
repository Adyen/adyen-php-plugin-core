<?php

namespace Adyen\Core\Tests\BusinessLogic\WebhookAPI\MockComponents;

use Adyen\Core\BusinessLogic\Domain\Webhook\Models\WebhookConfig;
use Adyen\Core\BusinessLogic\Domain\Webhook\Repositories\WebhookConfigRepository;

/**
 * Class MockWebhookConfigReposotory
 *
 * @package Adyen\Core\Tests\BusinessLogic\WebhookAPI\MockComponents
 */
class MockWebhookConfigReposotory implements WebhookConfigRepository
{
    /**
     * @var WebhookConfig
     */
    private $webhookConfig;

    public function __construct()
    {
        $this->webhookConfig = new WebhookConfig('ID', 'testMerchantId', true, 'username', 'password', '44782DEF547AAA06C910C43932B1EB0C71FC68D9D0C057550C48EC2ACF6BA056');
    }

    /**
     * @inheritDoc
     */
    public function getWebhookConfig(): ?WebhookConfig
    {
        return $this->webhookConfig;
    }

    /**
     * @inheritDoc
     */
    public function setWebhookConfig(?WebhookConfig $config): void
    {
        $this->webhookConfig = $config;
    }

    public function deleteWebhookConfig(): void
    {
    }
}
