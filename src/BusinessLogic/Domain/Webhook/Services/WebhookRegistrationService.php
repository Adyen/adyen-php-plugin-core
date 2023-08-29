<?php

namespace Adyen\Core\BusinessLogic\Domain\Webhook\Services;

use Adyen\Core\BusinessLogic\Domain\Integration\Webhook\WebhookUrlService;
use Adyen\Core\BusinessLogic\Domain\Merchant\Proxies\MerchantProxy;
use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Adyen\Core\BusinessLogic\Domain\Webhook\Exceptions\FailedToGenerateHmacException;
use Adyen\Core\BusinessLogic\Domain\Webhook\Exceptions\FailedToRegisterWebhookException;
use Adyen\Core\BusinessLogic\Domain\Webhook\Exceptions\MerchantDoesNotExistException;
use Adyen\Core\BusinessLogic\Domain\Webhook\Models\WebhookConfig;
use Adyen\Core\BusinessLogic\Domain\Webhook\Models\WebhookRequest;
use Adyen\Core\BusinessLogic\Domain\Webhook\Proxies\WebhookProxy;
use Adyen\Core\Infrastructure\Logger\Logger;
use Exception;

/**
 * Class WebhookRegistrationService
 *
 * @package Adyen\Core\BusinessLogic\Domain\Webhook\Services
 */
class WebhookRegistrationService
{
    /**
     * @var WebhookProxy
     */
    protected $proxy;
    /**
     * @var MerchantProxy
     */
    protected $merchantProxy;
    /**
     * @var WebhookUrlService
     */
    protected $webhookUrlService;

    /**
     * @param WebhookProxy $proxy
     * @param MerchantProxy $merchantProxy
     * @param WebhookUrlService $webhookUrlService
     */
    public function __construct(
        WebhookProxy      $proxy,
        MerchantProxy     $merchantProxy,
        WebhookUrlService $webhookUrlService
    )
    {
        $this->proxy = $proxy;
        $this->merchantProxy = $merchantProxy;
        $this->webhookUrlService = $webhookUrlService;
    }

    /**
     * Registers webhook.
     *
     * @param string $merchantId
     *
     * @return WebhookConfig
     *
     * @throws FailedToRegisterWebhookException
     */
    public function registerWebhook(string $merchantId): WebhookConfig
    {
        try {
            $merchant = $this->merchantProxy->getMerchantById($merchantId);

            if (!$merchant) {
                throw new MerchantDoesNotExistException(
                    new TranslatableLabel('Merchant with id %s not found.', 'webhooks.merchantError', [$merchantId])
                );
            }

            if (!$this->isWebhookRegistrationNeeded($merchantId)) {
                return $this->updateWebhook($merchantId);
            }

            $password = uniqid($merchantId . '_', true);

            $webhookRequest = new WebhookRequest(
                $this->webhookUrlService->getWebhookUrl(),
                $merchant->getMerchantName(),
                $password
            );

            $webhookConfig = $this->proxy->registerWebhook($merchantId, $webhookRequest);
            $webhookConfig->setPassword($password);

            return $webhookConfig;
        } catch (Exception $e) {
            Logger::logError($e->getMessage());

            throw new FailedToRegisterWebhookException(
                new TranslatableLabel(
                    'Failed to register webhook.',
                    'webhooks.failedToRegisterWebhook'
                ),
                $e
            );
        }
    }

    /**
     * @param string $merchantId
     * @param string $webhookId
     * @return string
     *
     * @throws FailedToGenerateHmacException
     */
    public function generateHmac(string $merchantId, string $webhookId): string
    {
        try {
            return $this->proxy->generateHMAC($merchantId, $webhookId);
        } catch (Exception $e) {
            Logger::logError($e->getMessage());

            throw new FailedToGenerateHmacException(
                new TranslatableLabel('Error generating HMAC.', 'webhooks.hmacError'),
                $e
            );
        }
    }

    /**
     * Check if webhook url is already registered.
     *
     * @param string $merchantId
     * @return bool
     */
    private function isWebhookRegistrationNeeded(string $merchantId): bool
    {
        $webhookUrls = $this->proxy->getWebhookURLs($merchantId);
        $url = $this->webhookUrlService->getWebhookUrl();

        return !in_array($url, $webhookUrls, true);
    }

    /**
     * @param string $merchantId
     *
     * @return WebhookConfig
     *
     * @throws Exception
     */
    private function updateWebhook(string $merchantId): WebhookConfig
    {
        $config = $this->proxy->getWebhookConfigFromUrl($merchantId, $this->webhookUrlService->getWebhookUrl());
        $password = uniqid($merchantId . '_', true);
        $webhookRequest = new WebhookRequest(
            $this->webhookUrlService->getWebhookUrl(),
            $config['username'],
            $password
        );
        $this->proxy->updateWebhook($merchantId, $config['id'], $webhookRequest);

        return new WebhookConfig(
            $config['id'] ?? '',
            $merchantId,
            $config['active'] ?? false,
            $config['username'] ?? '',
            $password
        );
    }
}
