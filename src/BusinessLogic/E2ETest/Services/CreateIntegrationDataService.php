<?php

namespace Adyen\Core\BusinessLogic\E2ETest\Services;

use Adyen\Core\BusinessLogic\AdminAPI\AdminAPI;
use Adyen\Core\BusinessLogic\AdminAPI\Connection\Request\ConnectionRequest;
use Adyen\Core\BusinessLogic\AdminAPI\GeneralSettings\Request\GeneralSettingsRequest;
use Adyen\Core\BusinessLogic\AdminAPI\Payment\Request\PaymentMethodRequest;
use Adyen\Core\BusinessLogic\AdyenAPI\Exceptions\ConnectionSettingsNotFoundException;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Currency;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\DataBag;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PaymentMethodCode;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\StartTransactionRequestContext;
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
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Exceptions\InvalidCaptureDelayException;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Exceptions\InvalidCaptureTypeException;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Exceptions\InvalidRetentionPeriodException;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Models\CaptureType;
use Adyen\Core\BusinessLogic\Domain\Merchant\Exceptions\ClientKeyGenerationFailedException;
use Adyen\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Adyen\Core\BusinessLogic\Domain\Payment\Exceptions\PaymentMethodDataEmptyException;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Services\TransactionHistoryService;
use Adyen\Core\BusinessLogic\Domain\Webhook\Exceptions\FailedToGenerateHmacException;
use Adyen\Core\BusinessLogic\Domain\Webhook\Exceptions\FailedToRegisterWebhookException;
use Adyen\Core\BusinessLogic\Domain\Webhook\Exceptions\MerchantDoesNotExistException;
use Adyen\Core\BusinessLogic\Domain\Webhook\Models\WebhookConfig;
use Adyen\Core\BusinessLogic\Domain\Webhook\Repositories\WebhookConfigRepository;
use Adyen\Core\Infrastructure\Configuration\ConfigurationManager;
use Adyen\Core\Infrastructure\Http\Exceptions\HttpRequestException;
use Adyen\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use Adyen\Core\Infrastructure\ServiceRegister;
use Exception;

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
     * Creates GeneralSettings in database
     *
     * @param bool $basketItemSync
     * @param string $captureType
     * @param string $captureDelay
     * @param string $shipmentStatus
     * @param string $retentionPeriod
     * @return void
     * @throws InvalidCaptureDelayException
     * @throws InvalidCaptureTypeException
     * @throws InvalidRetentionPeriodException
     */
    public function createGeneralSettingsConfiguration(
        bool   $basketItemSync = false,
        string $captureType = '',
        string $captureDelay = '',
        string $shipmentStatus = '',
        string $retentionPeriod = ''
    ): void
    {
        $generalSettingsData = $this->readFromJSONFile()['generalSettings'] ?? [];
        if (count($generalSettingsData) === 0) {
            return;
        }

        $generalSettingsRequest = new GeneralSettingsRequest(
            $basketItemSync ?? $generalSettingsData['basketItemSync'],
            $captureType ?? $generalSettingsData['capture'],
            $captureDelay ?? $generalSettingsData['captureDelay'],
            $shipmentStatus,
            $retentionPeriod ?? $generalSettingsData['retentionPeriod']
        );
        AdminAPI::get()->generalSettings(1)->saveGeneralSettings($generalSettingsRequest);
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
     * Saves test hostname in database
     *
     * @param string $url
     * @return void
     * @throws QueryFilterInvalidParamException
     */
    public function saveTestHostname(string $url): void
    {
        $host = parse_url($url)['host'];
        $this->getConfigurationManager()->saveConfigValue('testHostname', $host);
    }

    /**
     * Returns webhook config data
     *
     * @return array
     * @throws HttpRequestException
     * @throws Exception
     */
    public function getWebhookAuthorizationData(): array
    {
        /** @var WebhookConfig $webhookConfig */
        $webhookConfig = StoreContext::doWithStore(1, function () {
            return $this->getWebhookConfigRepository()->getWebhookConfig();
        });

        if (!$webhookConfig) {
            throw new HttpRequestException(
                'Hmac is undefined due to the unsuccessful creation of the webhook and hmac on the Adyen API.'
            );
        }

        $authData['username'] = $webhookConfig->getUsername();
        $authData['password'] = $webhookConfig->getPassword();
        $authData['hmac'] = $webhookConfig->getHmac();

        return $authData;
    }

    /**
     * Creates transaction history
     *
     * @param string $merchantReference
     * @param float $totalAmount
     * @param string $currency
     * @param CaptureType $captureType
     * @return void
     * @throws Exception
     */
    private function createTransactionHistoryForOrder(
        string      $merchantReference,
        float       $totalAmount,
        string      $currency,
        CaptureType $captureType
    ): void
    {
        StoreContext::doWithStore('1', static function () use (
            $merchantReference,
            $totalAmount,
            $currency,
            $captureType
        ) {
            $transactionContext = new StartTransactionRequestContext(
                PaymentMethodCode::parse('scheme'),
                Amount::fromFloat(
                    $totalAmount,
                    Currency::fromIsoCode(
                        $currency
                    )
                ),
                $merchantReference,
                '',
                new DataBag([]),
                new DataBag([])
            );
            /** @var TransactionHistoryService $transactionHistoryService */
            $transactionHistoryService = ServiceRegister::getService(TransactionHistoryService::class);
            $transactionHistoryService->createTransactionHistory($transactionContext->getReference(),
                $transactionContext->getAmount()->getCurrency(),
                $captureType
            );
        });
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

    /**
     * @return ConfigurationManager
     */
    private function getConfigurationManager(): ConfigurationManager
    {
        return ServiceRegister::getService(ConfigurationManager::CLASS_NAME);
    }

    /**
     * Returns WebhookConfigRepository instance
     *
     * @return WebhookConfigRepository
     */
    private function getWebhookConfigRepository(): WebhookConfigRepository
    {
        return ServiceRegister::getService(WebhookConfigRepository::class);
    }
}