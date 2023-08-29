<?php

namespace Adyen\Core\Tests\BusinessLogic\Domain\InfoSettings\Mocks;

use Adyen\Core\BusinessLogic\Domain\Webhook\Models\WebhookConfig;
use Adyen\Core\BusinessLogic\Domain\Webhook\Repositories\WebhookConfigRepository as WebhookConfigRepositoryInterface;

/**
 * Class MockWebhookConfigRepository
 *
 * @package Adyen\Core\Tests\BusinessLogic\Domain\InfoSettings\Mocks
 */
class MockWebhookConfigRepository implements WebhookConfigRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function getWebhookConfig(): ?WebhookConfig
    {
        return new WebhookConfig('1', 'testMerchantId', true, '1');
    }

    /**
     * @inheritDoc
     */
    public function setWebhookConfig(WebhookConfig $config): void
    {
    }

    public function deleteWebhookConfig(): void
    {
    }
}
