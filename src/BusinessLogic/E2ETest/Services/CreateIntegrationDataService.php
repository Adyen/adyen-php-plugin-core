<?php

namespace Adyen\Core\BusinessLogic\E2ETest\Services;

use Adyen\Core\BusinessLogic\AdminAPI\AdminAPI;
use Adyen\Core\BusinessLogic\AdminAPI\Connection\Request\ConnectionRequest;
use Adyen\Core\BusinessLogic\AdminAPI\Payment\Request\PaymentMethodRequest;
use Adyen\Core\BusinessLogic\AdyenAPI\Exceptions\ConnectionSettingsNotFoundException;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\ApiCredentialsDoNotExistException;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\ApiKeyCompanyLevelException;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\EmptyConnectionDataException;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\EmptyStoreException;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidAllowedOriginException;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidApiKeyException;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidConnectionSettingsException;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidModeException;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\MerchantIdChangedException;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\ModeChangedException;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\UserDoesNotHaveNecessaryRolesException;
use Adyen\Core\BusinessLogic\Domain\Merchant\Exceptions\ClientKeyGenerationFailedException;
use Adyen\Core\BusinessLogic\Domain\Payment\Exceptions\PaymentMethodDataEmptyException;
use Adyen\Core\BusinessLogic\Domain\Webhook\Exceptions\FailedToGenerateHmacException;
use Adyen\Core\BusinessLogic\Domain\Webhook\Exceptions\FailedToRegisterWebhookException;
use Adyen\Core\BusinessLogic\Domain\Webhook\Exceptions\MerchantDoesNotExistException;

/**
 * Class CreateIntegrationDataService
 *
 * @package Adyen\Core\BusinessLogic\E2ETest\Services
 */
class CreateIntegrationDataService
{
    const CREDIT_CARD = 'creditCard';
    const IDEAL = 'ideal';
    const KLARNA_PAY_NOW = 'klarnaPayNow';
    const KLARNA_PAY_LATER = 'klarnaPayLater';
    const KLARNA_PAY_OVERTIME = 'klarnaPayOverTime';
    const TWINT = 'twint';
    const BANCONTACT_MODILE = 'bancontact';
    const PAYPAL = 'payPal';
    const APPLE_PAY = 'applePay';

    /**
     * @var string
     */
    private $testFilePath;

    public function __construct(string $testFilePath)
    {
        $this->testFilePath = $testFilePath;
    }

    /**
     * Creates all payment methods from test data file
     *
     * @return void
     * @throws PaymentMethodDataEmptyException
     */
    public function createAllPaymentMethodsFromTestData(): void
    {
        $this->createPaymentMethodConfiguration(self::CREDIT_CARD);
        $this->createPaymentMethodConfiguration(self::IDEAL);
        $this->createPaymentMethodConfiguration(self::KLARNA_PAY_NOW);
        $this->createPaymentMethodConfiguration(self::KLARNA_PAY_LATER);
        $this->createPaymentMethodConfiguration(self::KLARNA_PAY_OVERTIME);
        $this->createPaymentMethodConfiguration(self::TWINT);
        $this->createPaymentMethodConfiguration(self::BANCONTACT_MODILE);
        $this->createPaymentMethodConfiguration(self::PAYPAL);
        $this->createPaymentMethodConfiguration(self::APPLE_PAY);
    }

    /**
     * Creates ConnectionSettings and WebhookConfig in database
     *
     * @throws EmptyConnectionDataException
     * @throws MerchantDoesNotExistException
     * @throws ApiKeyCompanyLevelException
     * @throws InvalidModeException
     * @throws EmptyStoreException
     * @throws InvalidApiKeyException
     * @throws MerchantIdChangedException
     * @throws ClientKeyGenerationFailedException
     * @throws FailedToGenerateHmacException
     * @throws UserDoesNotHaveNecessaryRolesException
     * @throws InvalidAllowedOriginException
     * @throws ApiCredentialsDoNotExistException
     * @throws InvalidConnectionSettingsException
     * @throws ModeChangedException
     * @throws ConnectionSettingsNotFoundException
     * @throws FailedToRegisterWebhookException
     */
    public function createConnectionAndWebhookConfiguration(string $testApiKey): void
    {
        $authorizationData = $this->readFromJSONFile()['authorizationData'] ?? [];
        if (count($authorizationData) === 0) {
            return;
        }

        $connectionRequest = new ConnectionRequest(
            $authorizationData['storeId'],
            $authorizationData['mode'],
            $testApiKey,
            "",
            $authorizationData['liveAPiKey'],
            $authorizationData['liveMerchantId']
        );
        AdminAPI::get()->connection(1)->connect($connectionRequest);

        $connectionRequest = new ConnectionRequest(
            $authorizationData['storeId'],
            $authorizationData['mode'],
            $testApiKey,
            $authorizationData['testMerchantId'],
            $authorizationData['liveAPiKey'],
            $authorizationData['liveMerchantId']
        );
        AdminAPI::get()->connection(1)->connect($connectionRequest);
    }

    /**
     * Creates PaymentMethod configuration in database
     *
     * @throws PaymentMethodDataEmptyException
     */
    public function createPaymentMethodConfiguration(string $paymentMethod): void
    {
        $method = PaymentMethodRequest::parse($this->readFromJSONFile()[$paymentMethod] ?? []);
        AdminAPI::get()->payment(1)->saveMethodConfiguration($method);
    }

    /**
     * Reads from json file
     *
     * @return array
     */
    protected function readFromJSONFile(): array
    {
        $jsonString = file_get_contents(
            $this->testFilePath . '/vendor/adyen/integration-core/src/BusinessLogic' .
            '/E2ETest/Data/integration_config_test_data.json',
            FILE_USE_INCLUDE_PATH
        );

        return json_decode($jsonString, true);
    }
}