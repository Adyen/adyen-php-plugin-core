<?php

namespace Adyen\Core\Tests\BusinessLogic\Common\MockComponents;

use Adyen\Core\BusinessLogic\Domain\Webhook\Models\WebhookConfig;
use Adyen\Core\BusinessLogic\Domain\Webhook\Repositories\WebhookConfigRepository;

/**
 * Class MockWebhookConfigRepository.
 *
 * @package Adyen\Core\Tests\BusinessLogic\Common\MockComponents
 */
class MockWebhookConfigRepository implements WebhookConfigRepository
{
    /**
     * @var ?WebhookConfig
     */
    private $config;

    /**
     * @inheritDoc
     */
    public function getWebhookConfig(): ?WebhookConfig
    {
        return $this->config;
    }

    /**
     * @inheritDoc
     */
    public function setWebhookConfig(WebhookConfig $config): void
    {
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function deleteWebhookConfig(): void
    {
    }
}
