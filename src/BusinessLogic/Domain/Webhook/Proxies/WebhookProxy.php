<?php

namespace Adyen\Core\BusinessLogic\Domain\Webhook\Proxies;

use Adyen\Core\BusinessLogic\Domain\Webhook\Models\WebhookRequest;
use Adyen\Core\BusinessLogic\Domain\Webhook\Models\WebhookConfig;
use Exception;

/**
 * Class WebhookProxy
 *
 * @package Adyen\Core\BusinessLogic\Domain\Webhook\Proxies
 */
interface WebhookProxy
{
    /**
     * Registers webhook.
     *
     * @param string $merchantId
     * @param WebhookRequest $webhook
     *
     * @return WebhookConfig
     *
     * @throws Exception
     *
     */
    public function registerWebhook(string $merchantId, WebhookRequest $webhook): WebhookConfig;

    /**
     * Deletes registered webhook.
     *
     * @param string $merchantId
     * @param string $webhookId
     *
     * @return void
     *
     * @throws Exception
     *
     */
    public function deleteWebhook(string $merchantId, string $webhookId): void;

    /**
     * Generates HMAC key.
     *
     * @param string $merchantId
     * @param string $webhookId
     *
     * @return string
     *
     * @throws Exception
     */
    public function generateHMAC(string $merchantId, string $webhookId): string;

    /**
     * Returns array of registered urls.
     *
     * @param string $merchantId
     *
     * @return string[]
     */
    public function getWebhookURLs(string $merchantId): array;

    /**
     * @param string $merchantId
     * @param string $webhookId
     *
     * @param WebhookRequest $webhook
     *
     * @return void
     */
    public function updateWebhook(string $merchantId, string $webhookId, WebhookRequest $webhook): void;

    /**
     * Validates webhook for current store context. Returns empty array in case of error.
     *
     * @param string $merchantId
     * @param string $webhookId
     *
     * @return string
     *
     * @throws Exception
     */
    public function testWebhook(string $merchantId, string $webhookId): string;
}
