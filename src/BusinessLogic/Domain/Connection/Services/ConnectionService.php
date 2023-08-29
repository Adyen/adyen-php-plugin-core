<?php

namespace Adyen\Core\BusinessLogic\Domain\Connection\Services;

use Adyen\Core\BusinessLogic\AdyenAPI\Exceptions\ConnectionSettingsNotFoundException;
use Adyen\Core\BusinessLogic\AdyenAPI\Management\Connection\Http\Proxy;
use Adyen\Core\BusinessLogic\AdyenAPI\Management\ProxyFactory;
use Adyen\Core\BusinessLogic\Domain\Connection\Enums\Mode;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\ApiCredentialsDoNotExistException;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\ApiKeyCompanyLevelException;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidAllowedOriginException;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidApiKeyException;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidConnectionSettingsException;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\ModeChangedException;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\UserDoesNotHaveNecessaryRolesException;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionSettings;
use Adyen\Core\BusinessLogic\Domain\Connection\Proxies\ConnectionProxy;
use Adyen\Core\BusinessLogic\Domain\Connection\Repositories\ConnectionSettingsRepository;
use Adyen\Core\BusinessLogic\Domain\Disconnect\Services\DisconnectService;
use Adyen\Core\BusinessLogic\Domain\InfoSettings\Services\ValidationService;
use Adyen\Core\BusinessLogic\Domain\Integration\Store\StoreService;
use Adyen\Core\BusinessLogic\Domain\Merchant\Exceptions\ClientKeyGenerationFailedException;
use Adyen\Core\BusinessLogic\Domain\Merchant\Exceptions\ClientPrefixDoesNotExistException;
use Adyen\Core\BusinessLogic\Domain\Merchant\Services\MerchantService;
use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Adyen\Core\BusinessLogic\Domain\Webhook\Exceptions\FailedToGenerateHmacException;
use Adyen\Core\BusinessLogic\Domain\Webhook\Exceptions\FailedToRegisterWebhookException;
use Adyen\Core\BusinessLogic\Domain\Webhook\Exceptions\MerchantDoesNotExistException;
use Adyen\Core\BusinessLogic\Domain\Webhook\Repositories\WebhookConfigRepository;
use Adyen\Core\BusinessLogic\Domain\Webhook\Services\WebhookRegistrationService;
use Adyen\Core\BusinessLogic\WebhookAPI\Exceptions\InvalidWebhookException;
use Adyen\Core\Infrastructure\ServiceRegister;
use Exception;

/**
 * Class AuthorizationService
 *
 * @package Adyen\Core\BusinessLogic\Domain\Connection\Services
 */
class ConnectionService
{
    /**
     * @var ConnectionSettingsRepository
     */
    private $connectionSettingsRepository;
    /**
     * @var StoreService
     */
    private $storeService;
    /**
     * @var WebhookConfigRepository
     */
    private $webhookConfigRepository;
    /**
     * @var ConnectionValidator
     */
    private $connectionValidator;

    /**
     * @param ConnectionSettingsRepository $connectionSettingsRepository
     * @param StoreService $storeService
     * @param WebhookConfigRepository $webhookConfigRepository
     */
    public function __construct(
        ConnectionSettingsRepository $connectionSettingsRepository,
        StoreService                 $storeService,
        WebhookConfigRepository      $webhookConfigRepository
    )
    {
        $this->connectionSettingsRepository = $connectionSettingsRepository;
        $this->storeService = $storeService;
        $this->webhookConfigRepository = $webhookConfigRepository;
        $this->connectionValidator = new ConnectionValidator($connectionSettingsRepository);
    }

    /**
     * Check if user is loggedIn for specific store.
     *
     * @return bool
     *
     * @throws ApiCredentialsDoNotExistException
     * @throws ApiKeyCompanyLevelException
     * @throws ConnectionSettingsNotFoundException
     * @throws InvalidApiKeyException
     * @throws InvalidConnectionSettingsException
     * @throws ModeChangedException
     * @throws UserDoesNotHaveNecessaryRolesException
     * @throws MerchantDoesNotExistException
     */
    public function isLoggedIn(): bool
    {
        $connectionSettings = $this->connectionSettingsRepository->getConnectionSettings();

        if ($connectionSettings && empty($connectionSettings->getActiveConnectionData()->getMerchantId())) {
            $this->connectionValidator->validateApiKey($connectionSettings);
            $this->saveConnectionSettings($connectionSettings);
        }

        if ($connectionSettings && $this->validateCredentials($connectionSettings->getActiveConnectionData())) {
            $this->validateConnection($connectionSettings);

            return true;
        }

        return false;
    }

    /**
     * Retrieves connection settings.
     *
     * @return ConnectionSettings|null
     */
    public function getConnectionData(): ?ConnectionSettings
    {
        return $this->connectionSettingsRepository->getConnectionSettings();
    }

    /**
     * Saves connection data.
     *
     * @param ConnectionSettings $connectionSettings
     *
     * @return void
     *
     * @throws ApiCredentialsDoNotExistException
     * @throws ApiKeyCompanyLevelException
     * @throws ClientKeyGenerationFailedException
     * @throws ConnectionSettingsNotFoundException
     * @throws FailedToGenerateHmacException
     * @throws FailedToRegisterWebhookException
     * @throws InvalidAllowedOriginException
     * @throws InvalidApiKeyException
     * @throws InvalidConnectionSettingsException
     * @throws ModeChangedException
     * @throws UserDoesNotHaveNecessaryRolesException
     * @throws MerchantDoesNotExistException
     * @throws ClientPrefixDoesNotExistException
     */
    public function saveConnectionData(ConnectionSettings $connectionSettings): void
    {
        $this->validateConnection($connectionSettings);
        $existingSettings = $this->connectionSettingsRepository->getConnectionSettings();

        if (!$existingSettings || empty($connectionSettings->getActiveConnectionData()->getMerchantId())) {
            $this->saveConnectionSettings($connectionSettings);
            $existingSettings = $connectionSettings;
        }

        if (!empty($connectionSettings->getActiveConnectionData()->getMerchantId())) {
            $this->initializeConnection($connectionSettings, $existingSettings);
        }
    }

    /**
     * Validates connection data for specific store.
     *
     * @throws ApiCredentialsDoNotExistException
     * @throws ApiKeyCompanyLevelException
     * @throws ConnectionSettingsNotFoundException
     * @throws InvalidApiKeyException
     * @throws InvalidConnectionSettingsException
     * @throws ModeChangedException
     * @throws UserDoesNotHaveNecessaryRolesException
     * @throws MerchantDoesNotExistException
     */
    public function validateConnection(ConnectionSettings $connectionSettings): void
    {
        $this->connectionValidator->validateConnectionSettings($connectionSettings);
        $this->connectionValidator->validateApiKey($connectionSettings);
    }

    /**
     * @param ConnectionSettings $connectionSettings
     * @param ConnectionSettings $existingSettings
     *
     * @return void
     *
     * @throws ClientKeyGenerationFailedException
     * @throws ClientPrefixDoesNotExistException
     * @throws ConnectionSettingsNotFoundException
     * @throws FailedToGenerateHmacException
     * @throws FailedToRegisterWebhookException
     * @throws InvalidAllowedOriginException
     * @throws Exception
     * @noinspection NullPointerExceptionInspection
     *
     */
    private function initializeConnection(ConnectionSettings $connectionSettings, ConnectionSettings $existingSettings): void
    {

        if ($this->connectionValidator->isApiKeyChanged($connectionSettings, $existingSettings) &&
            $this->connectionValidator->isModeChanged($connectionSettings, $existingSettings)) {
            $this->getDisconnectService()->disconnect();
            $this->saveConnectionSettings($connectionSettings);

            return;
        }

        if ($this->connectionValidator->isApiKeyChanged($connectionSettings, $existingSettings) &&
            !$this->connectionValidator->isModeChanged($connectionSettings, $existingSettings)) {
            $this->saveConnectionSettings($connectionSettings);
        }

        $this->initialize($connectionSettings);
    }

    /**
     * @param ConnectionSettings $connectionSettings
     *
     * @return void
     *
     * @throws ClientKeyGenerationFailedException
     * @throws ClientPrefixDoesNotExistException
     * @throws ConnectionSettingsNotFoundException
     * @throws FailedToGenerateHmacException
     * @throws FailedToRegisterWebhookException
     * @throws InvalidAllowedOriginException
     */
    private function initialize(ConnectionSettings $connectionSettings): void
    {
        $connectionData = $connectionSettings->getActiveConnectionData();
        $merchantId = $connectionData->getMerchantId();
        $this->addAllowedOrigin($connectionSettings);
        $clientKey = $this->generateClientKey($connectionData);
        $this->initializeWebhook($merchantId);
        $connectionData->setClientKey($clientKey);

        if ($connectionSettings->getMode() === Mode::MODE_LIVE) {
            $connectionData->setClientPrefix($this->getMerchantService()->getLivePrefix($merchantId));
            $connectionSettings->setLiveData($connectionData);
        }

        if ($connectionSettings->getMode() === Mode::MODE_TEST) {
            $connectionSettings->setTestData($connectionData);
        }

        $this->saveConnectionSettings($connectionSettings);
    }

    /**
     * @param ConnectionData $connectionData
     *
     * @return string
     *
     * @throws ClientKeyGenerationFailedException
     */
    private function generateClientKey(ConnectionData $connectionData): string
    {
        $allConnectionSettings = $this->connectionSettingsRepository->getAllConnectionSettings();

        foreach ($allConnectionSettings as $connectionSettings) {
            if (!empty($connectionSettings->getActiveConnectionData()->getClientKey()) &&
                $connectionSettings->getActiveConnectionData()->getApiKey() === $connectionData->getApiKey()) {
                return $connectionSettings->getActiveConnectionData()->getClientKey();
            }
        }

        return $this->getMerchantService()->generateClientKey();
    }

    /**
     * @param string $merchantId
     *
     * @return void
     *
     * @throws FailedToGenerateHmacException
     * @throws FailedToRegisterWebhookException
     * @throws Exception
     */
    private function initializeWebhook(string $merchantId): void
    {
        $webhookConfig = $this->getWebhookService()->registerWebhook($merchantId);
        $hmac = $this->getWebhookService()->generateHmac($merchantId, $webhookConfig->getId());
        $webhookConfig->setHmac($hmac);

        $this->webhookConfigRepository->setWebhookConfig($webhookConfig);
        if (!$this->getValidationService()->validateWebhook()) {
            throw new InvalidWebhookException('Webhook validation failed.');
        }
    }

    /**
     * @param ConnectionSettings $connectionSettings
     *
     * @return void
     *
     * @throws ConnectionSettingsNotFoundException
     * @throws InvalidAllowedOriginException
     */
    private function addAllowedOrigin(ConnectionSettings $connectionSettings)
    {
        $storeDomain = $this->storeService->getStoreDomain();
        $proxy = $this->getProxy($connectionSettings);

        if (!$proxy->hasAllowedOrigin($storeDomain) &&
            !$proxy->addAllowedOrigin($storeDomain)) {
            $this->connectionSettingsRepository->deleteConnectionSettings();

            throw new InvalidAllowedOriginException(
                new TranslatableLabel('Adding allowed origin failed', 'connection.originFailed')
            );
        }
    }

    /**
     * Saves connection settings.
     *
     * @param ConnectionSettings $connectionSettings
     *
     * @return void
     */
    private function saveConnectionSettings(ConnectionSettings $connectionSettings): void
    {
        $this->connectionSettingsRepository->setConnectionSettings($connectionSettings);
    }

    /**
     * Check if API key and MerchantId are valid.
     *
     * @param ?ConnectionData $connectionData
     *
     * @return bool
     */
    private function validateCredentials(?ConnectionData $connectionData): bool
    {
        return $connectionData && !empty($connectionData->getApiKey()) && !empty($connectionData->getMerchantId());
    }

    /**
     * @param ConnectionSettings $connectionSettings
     *
     * @return ConnectionProxy
     *
     * @throws ConnectionSettingsNotFoundException
     */
    private function getProxy(ConnectionSettings $connectionSettings): ConnectionProxy
    {
        return ProxyFactory::makeProxy(Proxy::class, $connectionSettings);
    }

    /**
     * @return MerchantService
     */
    private function getMerchantService(): MerchantService
    {
        return ServiceRegister::getService(MerchantService::class);
    }

    /**
     * @return WebhookRegistrationService
     */
    private function getWebhookService(): WebhookRegistrationService
    {
        return ServiceRegister::getService(WebhookRegistrationService::class);
    }

    /**
     * @return DisconnectService
     */
    private function getDisconnectService(): DisconnectService
    {
        return ServiceRegister::getService(DisconnectService::class);
    }

    /**
     * @return ValidationService
     */
    private function getValidationService(): ValidationService
    {
        return ServiceRegister::getService(ValidationService::class);
    }
}
