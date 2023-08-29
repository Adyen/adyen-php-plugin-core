<?php

namespace Adyen\Core\BusinessLogic\Domain\Webhook\Repositories;

use Adyen\Core\BusinessLogic\Domain\Webhook\Models\WebhookConfig;
use Exception;

/**
 * Class WebhookConfigRepository
 *
 * @package Adyen\Core\BusinessLogic\Domain\Webhook\Repositories
 */
interface WebhookConfigRepository
{
    /**
     * Retrieves webhook config.
     *
     * @throws Exception
     *
     * @return WebhookConfig | null
     */
    public function getWebhookConfig(): ?WebhookConfig;

    /**
     * Saves webhook config.
     *
     * @param WebhookConfig $config
     *
     * @throws Exception
     *
     * @return void
     */
    public function setWebhookConfig(WebhookConfig $config): void;

    /**
     * Removes webhook config.
     *
     * @throws Exception
     *
     * @return void
     */
    public function deleteWebhookConfig(): void;
}
