<?php

namespace Adyen\Core\BusinessLogic\Domain\InfoSettings\Services;

use Adyen\Core\BusinessLogic\Domain\Webhook\Proxies\WebhookProxy;
use Adyen\Core\BusinessLogic\Domain\Webhook\Repositories\WebhookConfigRepository;
use Exception;

/**
 * Class ValidationService
 *
 * @package Adyen\Core\BusinessLogic\Domain\InfoSettings\Services
 */
class ValidationService
{
    /**
     * @var WebhookProxy
     */
    private $webhookProxy;

    /**
     * @var WebhookConfigRepository
     */
    private $webhookConfigRepository;

    /**
     * @param WebhookProxy $webhookProxy
     * @param WebhookConfigRepository $repository
     */
    public function __construct(WebhookProxy $webhookProxy, WebhookConfigRepository $repository)
    {
        $this->webhookProxy = $webhookProxy;
        $this->webhookConfigRepository = $repository;
    }

    /**
     * @return bool
     *
     * @throws Exception
     */
    public function validateWebhook(): bool
    {
        $webhookConfig = $this->webhookConfigRepository->getWebhookConfig();

        if (!$webhookConfig) {
            return false;
        }

        $test = json_decode(
            $this->webhookProxy->testWebhook(
                $webhookConfig->getMerchantId(),
                $webhookConfig->getId()
            ),
            true
        );

        return !empty($test['data'][0]['status']) && $test['data'][0]['status'] === 'success';
    }

    /**
     * @return string
     *
     * @throws Exception
     */
    public function validationReport(): string
    {
        $webhookConfig = $this->webhookConfigRepository->getWebhookConfig();

        if (!$webhookConfig) {
            return 'Webhook config not found!';
        }

        return $this->webhookProxy->testWebhook(
            $webhookConfig->getMerchantId(),
            $webhookConfig->getId()
        );
    }
}
