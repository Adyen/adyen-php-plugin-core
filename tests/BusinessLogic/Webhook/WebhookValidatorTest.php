<?php

namespace Adyen\Core\Tests\BusinessLogic\Webhook;

use Adyen\Core\BusinessLogic\Bootstrap\SingleInstance;
use Adyen\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Adyen\Core\BusinessLogic\Domain\Webhook\Exceptions\WebhookConfigDoesntExistException;
use Adyen\Core\BusinessLogic\Domain\Webhook\Models\WebhookConfig;
use Adyen\Core\BusinessLogic\Domain\Webhook\Repositories\WebhookConfigRepository;
use Adyen\Core\BusinessLogic\Webhook\Validator\WebhookValidator;
use Adyen\Core\BusinessLogic\WebhookAPI\Exceptions\InvalidWebhookException;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Adyen\Core\Tests\BusinessLogic\WebhookAPI\MockComponents\MockWebhookConfigReposotory;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;
use Adyen\Webhook\Exception\AuthenticationException;
use Adyen\Webhook\Exception\MerchantAccountCodeException;
use Exception;

/**
 * Class WebhookValidatorTest
 *
 * @package Adyen\Core\Tests\BusinessLogic\Domain\Webhook\Services
 */
class WebhookValidatorTest extends BaseTestCase
{
    /**
     * @var WebhookConfigRepository
     */
    private $webhookConfigRepository;

    /**
     * @var WebhookValidator
     */
    private $validator;

    /**
     * @var string
     */
    private $payload;

    public function setUp(): void
    {
        parent::setUp();

        $this->payload = json_decode(
            file_get_contents(__DIR__ . '/../Common/ApiResponses/Webhook/webhookPayload.json'),
            true
        );

        $this->webhookConfigRepository = new MockWebhookConfigReposotory();

        TestServiceRegister::registerService(
            WebhookConfigRepository::class,
            new SingleInstance(function () {
                return $this->webhookConfigRepository;
            })
        );

        $this->validator = TestServiceRegister::getService(WebhookValidator::class);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testWebhookConfigDoesntExistException(): void
    {
        // arrange
        $this->webhookConfigRepository->setWebhookConfig(null);

        $this->expectException(WebhookConfigDoesntExistException::class);
        $this->expectExceptionMessage('Webhook config is not found in database.');

        // act
        StoreContext::doWithStore('1', [$this->validator, 'validate'], [$this->payload]);
        // assert
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testMerchantAccountCodeException(): void
    {
        // arrange
        $this->payload =
            json_decode(
                file_get_contents(__DIR__ . '/../Common/ApiResponses/Webhook/merchantAccountCodeMissing.json'),
                true
            );

        $_SERVER['PHP_AUTH_USER'] = 'username';
        $_SERVER['PHP_AUTH_PW'] = 'password';

        $this->expectException(MerchantAccountCodeException::class);
        $this->expectExceptionMessage('merchantAccountCode is empty in settings or in the notification');

        // act
        StoreContext::doWithStore('1', [$this->validator, 'validate'], [$this->payload]);
        // assert
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testMerchantAccountEmpty(): void
    {
        // arrange
        $this->payload = json_decode(
            file_get_contents(__DIR__ . '/../Common/ApiResponses/Webhook/merchantAccountEmpty.json'),
            true
        );
        $_SERVER['PHP_AUTH_USER'] = 'username';
        $_SERVER['PHP_AUTH_PW'] = 'password';
        $this->webhookConfigRepository->setWebhookConfig(new WebhookConfig('ID', 'testMerchantId', true, 'username', 'password'));
        $this->expectException(InvalidWebhookException::class);
        $this->expectExceptionMessage('Webhook validation failed.');

        // act
        StoreContext::doWithStore('1', [$this->validator, 'validate'], [$this->payload]);
        // assert
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testAuthenticationException(): void
    {
        // arrange
        $this->payload = json_decode(
            file_get_contents(__DIR__ . '/../Common/ApiResponses/Webhook/authenticationException.json'),
            true
        );
        unset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Authentication failed: PHP_AUTH_USER or PHP_AUTH_PW are empty.');

        // act
        StoreContext::doWithStore('1', [$this->validator, 'validate'], [$this->payload]);
        // assert
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testAuthenticationCredentialsNotFound(): void
    {
        // arrange
        unset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
        $this->expectException(InvalidWebhookException::class);
        $this->expectExceptionMessage('Webhook validation failed.');
        // act
        StoreContext::doWithStore('1', [$this->validator, 'validate'], [$this->payload]);
        // assert
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testAuthenticationExceptionInvalidCredentials(): void
    {
        // arrange
        $this->payload = json_decode(
            file_get_contents(__DIR__ . '/../Common/ApiResponses/Webhook/invalidCredentials.json'),
            true
        );
        $_SERVER['PHP_AUTH_USER'] = 'user';
        $_SERVER['PHP_AUTH_PW'] = 'pass';
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('username and\or password are not the same as in settings');
        // act
        StoreContext::doWithStore('1', [$this->validator, 'validate'], [$this->payload]);
        // assert
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testAuthenticationFailInvalidCredentials(): void
    {
        // arrange
        $_SERVER['PHP_AUTH_USER'] = 'user';
        $_SERVER['PHP_AUTH_PW'] = 'pass';
        $this->expectException(InvalidWebhookException::class);
        $this->expectExceptionMessage('Webhook validation failed.');
        // act
        StoreContext::doWithStore('1', [$this->validator, 'validate'], [$this->payload]);
        // assert
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testInvalidEventCode(): void
    {
        // arrange
        $this->payload = json_decode(
            file_get_contents(__DIR__ . '/../Common/ApiResponses/Webhook/invalidEventCode.json'),
            true
        );
        $_SERVER['PHP_AUTH_USER'] = 'username';
        $_SERVER['PHP_AUTH_PW'] = 'password';
        $this->expectException(InvalidWebhookException::class);
        $this->expectExceptionMessage('Webhook validation failed.');
        // act
        StoreContext::doWithStore('1', [$this->validator, 'validate'], [$this->payload]);
        // assert
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testHmac(): void
    {
        // arrange
        $payload = json_decode(
            file_get_contents(__DIR__ . '/../Common/ApiResponses/Webhook/hmac.json'),
            true
        );
        $_SERVER['PHP_AUTH_USER'] = 'username';
        $_SERVER['PHP_AUTH_PW'] = 'password';
        $this->webhookConfigRepository->setWebhookConfig(new WebhookConfig(
            'ID',
            'testMerchantId',
            true,
            'username',
            'password',
            '54842DEF547AAA06C910C43932B1EB0C71FC68D9D0C057550C48EC2ACF6BA056'
        ));
        $this->expectException(InvalidWebhookException::class);
        $this->expectExceptionMessage('Webhook validation failed.');

        // act
        StoreContext::doWithStore('1', [$this->validator, 'validate'], [$payload]);
        // assert
    }
}
