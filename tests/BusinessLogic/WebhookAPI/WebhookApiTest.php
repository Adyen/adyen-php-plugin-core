<?php

namespace Adyen\Core\Tests\BusinessLogic\WebhookAPI;

use Adyen\Core\BusinessLogic\Bootstrap\SingleInstance;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\InvalidCurrencyCode;
use Adyen\Core\BusinessLogic\Domain\Integration\Order\OrderService;
use Adyen\Core\BusinessLogic\Domain\Webhook\Exceptions\WebhookConfigDoesntExistException;
use Adyen\Core\BusinessLogic\Domain\Webhook\Repositories\WebhookConfigRepository;
use Adyen\Core\BusinessLogic\WebhookAPI\WebhookAPI;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Adyen\Core\Tests\BusinessLogic\WebhookAPI\MockComponents\MockOrderService;
use Adyen\Core\Tests\BusinessLogic\WebhookAPI\MockComponents\MockWebhookConfigReposotory;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;
use Adyen\Webhook\Exception\AuthenticationException;
use Adyen\Webhook\Exception\HMACKeyValidationException;
use Adyen\Webhook\Exception\InvalidDataException;
use Adyen\Webhook\Exception\MerchantAccountCodeException;

/**
 * Class WebhookApiTest
 *
 * @package Adyen\Core\Tests\BusinessLogic\WebhookAPI
 */
class WebhookApiTest extends BaseTestCase
{
    /**
     * @var WebhookConfigRepository
     */
    private $webhookConfigRepository;

    /**
     * @var string
     */
    private $payload;

    /**
     * @var OrderService
     */
    private $orderService;

    public function setUp(): void
    {
        parent::setUp();

        $_SERVER['PHP_AUTH_USER'] = 'username';
        $_SERVER['PHP_AUTH_PW'] = 'password';

        $this->payload = json_decode(
            file_get_contents(__DIR__ . '/../Common/ApiResponses/Webhook/webhookPayload.json'),
            true
        );

        $this->webhookConfigRepository = new  MockWebhookConfigReposotory();
        $this->orderService = new MockOrderService();

        TestServiceRegister::registerService(
            WebhookConfigRepository::class,
            new SingleInstance(function () {
                return $this->webhookConfigRepository;
            })
        );

        TestServiceRegister::registerService(
            OrderService::class,
            new SingleInstance(function () {
                return $this->orderService;
            })
        );
    }

    /**
     * @return void
     *
     * @throws InvalidCurrencyCode
     * @throws WebhookConfigDoesntExistException
     * @throws AuthenticationException
     * @throws HMACKeyValidationException
     * @throws InvalidDataException
     * @throws MerchantAccountCodeException
     */
    public function testIsResponseSuccessful(): void
    {
        // Arrange
        // Act
        $response = WebhookAPI::get()->webhookHandler('1')->handleRequest($this->payload);
        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @return void
     *
     * @throws InvalidCurrencyCode
     * @throws WebhookConfigDoesntExistException
     * @throws AuthenticationException
     * @throws HMACKeyValidationException
     * @throws InvalidDataException
     * @throws MerchantAccountCodeException
     */
    public function testIsResponseFail(): void
    {
        // Arrange
        $this->payload = json_decode(
            file_get_contents(__DIR__ . '/../Common/ApiResponses/Webhook/invalidCredentials.json'),
            true
        );
        // Act
        $response = WebhookAPI::get()->webhookHandler('1')->handleRequest(
            $this->payload
        );
        // Assert
        self::assertFalse($response->isSuccessful());
    }
}
