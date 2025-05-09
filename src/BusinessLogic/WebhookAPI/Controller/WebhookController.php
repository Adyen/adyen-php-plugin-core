<?php

namespace Adyen\Core\BusinessLogic\WebhookAPI\Controller;

use Adyen\Core\BusinessLogic\AdminAPI\Response\Response;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\InvalidCurrencyCode;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Currency;
use Adyen\Core\BusinessLogic\Domain\Webhook\Exceptions\WebhookConfigDoesntExistException;
use Adyen\Core\BusinessLogic\Domain\Webhook\Models\Webhook;
use Adyen\Core\BusinessLogic\Webhook\Handler\WebhookHandler;
use Adyen\Core\BusinessLogic\Webhook\Validator\WebhookValidator;
use Adyen\Core\BusinessLogic\WebhookAPI\Exceptions\WebhookShouldRetryException;
use Adyen\Core\BusinessLogic\WebhookAPI\Response\WebhookSuccessResponse;
use Adyen\Core\BusinessLogic\WebhookAPI\Response\WebhookFailedResponse;
use Adyen\Webhook\EventCodes;
use Adyen\Webhook\Exception\AuthenticationException;
use Adyen\Webhook\Exception\HMACKeyValidationException;
use Adyen\Webhook\Exception\InvalidDataException;
use Adyen\Webhook\Exception\MerchantAccountCodeException;
use Exception;

/**
 * Class WebhookController
 *
 * @package Adyen\Core\BusinessLogic\WebhookAPI\Controller
 */
class WebhookController
{
    /**
     * @var WebhookValidator
     */
    private $validator;

    /**
     * @var WebhookHandler
     */
    private $handler;

    /**
     * @param WebhookValidator $validator
     * @param WebhookHandler $handler
     */
    public function __construct(WebhookValidator $validator, WebhookHandler $handler)
    {
        $this->validator = $validator;
        $this->handler = $handler;
    }

    /**
     * @param array $payload
     *
     * @return Response
     *
     * @throws InvalidCurrencyCode
     * @throws WebhookConfigDoesntExistException
     * @throws AuthenticationException
     * @throws HMACKeyValidationException
     * @throws InvalidDataException
     * @throws MerchantAccountCodeException
     * @throws WebhookShouldRetryException
     * @throws Exception
     */
    public function handleRequest(array $payload): Response
    {
        try {
            $this->validator->validate($payload);
            $this->handler->handle($this->fromPayload($payload));

            return new WebhookSuccessResponse();
        } catch (WebhookShouldRetryException $e) {
            return new WebhookFailedResponse($e->getMessage());
        }
    }

    /**
     * Create domain Webhook from payload.
     *
     * @param array $payload
     *
     * @return Webhook
     *
     * @throws InvalidCurrencyCode
     */
    private function fromPayload(array $payload): Webhook
    {
        $notificationRequestItem = $payload['notificationItems'][0]['NotificationRequestItem'];
        $paymentMethod = $notificationRequestItem['paymentMethod'] ?? '';

        if ($notificationRequestItem['eventCode'] === EventCodes::ORDER_CLOSED) {
            $additionalData = $notificationRequestItem['additionalData'];
            ksort($additionalData);

            foreach ($additionalData as $key => $value) {
                if (strpos($key, 'paymentMethod') !== false) {
                    $paymentMethod = $value;
                }
            }
        }

        return new Webhook(
            Amount::fromInt(
                $notificationRequestItem['amount']['value'] ?? 0,
                $notificationRequestItem['amount']['currency'] ? Currency::fromIsoCode(
                    $notificationRequestItem['amount']['currency']
                ) : Currency::getDefault()
            ),
            $notificationRequestItem['eventCode'] ?? '',
            $notificationRequestItem['eventDate'] ?? '',
            $notificationRequestItem['additionalData']['hmacSignature'] ?? '',
            $notificationRequestItem['merchantAccountCode'] ?? '',
            $notificationRequestItem['merchantReference'] ?? '',
            $notificationRequestItem['pspReference'] ?? '',
            $paymentMethod,
            $notificationRequestItem['reason'] ?? '',
            $notificationRequestItem['success'] === 'true',
            $notificationRequestItem['originalReference'] ?? '',
            $notificationRequestItem['additionalData']['totalFraudScore'] ?? 0,
            $payload['live'] === 'true',
            $notificationRequestItem['additionalData'] ?? []
        );
    }
}
