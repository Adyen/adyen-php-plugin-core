<?php

namespace Adyen\Core\BusinessLogic\Webhook\Validator;

use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Adyen\Core\BusinessLogic\Domain\Webhook\Exceptions\WebhookConfigDoesntExistException;
use Adyen\Core\BusinessLogic\Domain\Webhook\Repositories\WebhookConfigRepository;
use Adyen\Core\BusinessLogic\WebhookAPI\Exceptions\InvalidWebhookException;
use Adyen\Webhook\Exception\AuthenticationException;
use Adyen\Webhook\Exception\HMACKeyValidationException;
use Adyen\Webhook\Exception\InvalidDataException;
use Adyen\Webhook\Exception\MerchantAccountCodeException;
use Adyen\Webhook\Receiver\HmacSignature;
use Adyen\Webhook\Receiver\NotificationReceiver;
use Exception;

/**
 * Class WebhookValidator
 *
 * @package Adyen\Core\BusinessLogic\Domain\Webhook\Services
 */
class WebhookValidator
{
    /**
     * @var WebhookConfigRepository
     */
    private $webhookConfigRepository;

    /**
     * @param WebhookConfigRepository $webhookConfigRepository
     */
    public function __construct(WebhookConfigRepository $webhookConfigRepository)
    {
        $this->webhookConfigRepository = $webhookConfigRepository;
    }

    /**
     * Validates webhook data using Adyen library.
     *
     * @param array $payload
     *
     * @return void
     *
     * @throws AuthenticationException
     * @throws HMACKeyValidationException
     * @throws InvalidDataException
     * @throws MerchantAccountCodeException
     * @throws WebhookConfigDoesntExistException
     * @throws Exception
     */
    public function validate(array $payload): void
    {
        $notificationRequestItem = $payload['notificationItems'][0]['NotificationRequestItem'] ?? [];
        $hmacSignature = new HmacSignature();
        $notificationReceiver = new NotificationReceiver($hmacSignature);
        $webhookConfig = $this->webhookConfigRepository->getWebhookConfig();

        if (!$webhookConfig) {
            throw new WebhookConfigDoesntExistException(
                new TranslatableLabel('Webhook config is not found in database.', 'webhooks.errorConfigDoesNotExist')
            );
        }

        // Webhook username is the same as the merchant id in connection settings, no need for connection data
        if (!$notificationReceiver->isAuthenticated(
            $notificationRequestItem,
            $webhookConfig->getUsername(),
            $webhookConfig->getUsername(),
            $webhookConfig->getPassword()
        )) {
            throw new InvalidWebhookException('Webhook validation failed. Invalid username and/or password');
        }

        if (!$hmacSignature->isHmacSupportedEventCode($notificationRequestItem)) {
            throw new InvalidWebhookException('Webhook validation failed. Unsupported event code');
        }

        if (!$notificationReceiver->validateHmac($notificationRequestItem, $webhookConfig->getHmac())
        ) {
            throw new InvalidWebhookException('Webhook validation failed. Invalid hmac signature.');
        }
    }
}
