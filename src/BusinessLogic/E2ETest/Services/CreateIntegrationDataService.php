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
    public function createPaymentMethodConfiguration(string $paymentMethod)
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
            './custom/plugins/AdyenPayment/vendor/adyen/integration-core/src/BusinessLogic' .
            '/E2ETest/Data/test_data.json',
            FILE_USE_INCLUDE_PATH
        );

        return json_decode($jsonString, true);
    }
}