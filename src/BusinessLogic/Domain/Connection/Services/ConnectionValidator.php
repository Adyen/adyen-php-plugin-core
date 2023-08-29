<?php

namespace Adyen\Core\BusinessLogic\Domain\Connection\Services;

use Adyen\Core\BusinessLogic\AdyenAPI\Exceptions\ConnectionSettingsNotFoundException;
use Adyen\Core\BusinessLogic\AdyenAPI\Management\Connection\Http\Proxy;
use Adyen\Core\BusinessLogic\AdyenAPI\Management\ProxyFactory;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\ApiCredentialsDoNotExistException;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\ApiKeyCompanyLevelException;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidApiKeyException;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidConnectionSettingsException;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\ModeChangedException;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\UserDoesNotHaveNecessaryRolesException;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionSettings;
use Adyen\Core\BusinessLogic\Domain\Connection\Proxies\ConnectionProxy;
use Adyen\Core\BusinessLogic\Domain\Connection\Repositories\ConnectionSettingsRepository;
use Adyen\Core\BusinessLogic\Domain\Merchant\Proxies\MerchantProxy;
use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Adyen\Core\BusinessLogic\Domain\Webhook\Exceptions\MerchantDoesNotExistException;

/**
 * Class ConnectionValidator
 *
 * @package Adyen\Core\BusinessLogic\Domain\Connection\Services
 */
class ConnectionValidator
{
    /**
     * Array of all necessary user roles.
     */
    private const NECESSARY_ROLES = [
        'Management API - Accounts read',
        'Management API - Webhooks read',
        'Management API - API credentials read and write',
        'Management API - Stores read',
        'Management API — Payment methods read',
        'Management API - Stores read and write',
        'Management API - Webhooks read and write',
        'Checkout encrypted cardholder data',
        'Merchant Recurring role',
        'Data Protection API',
        'Management API - Payout Account Settings Read',
        'Checkout webservice role',
        'Management API - Accounts read and write',
        'Merchant PAL Webservice role'
    ];
    /**
     * @var ConnectionSettingsRepository
     */
    private $connectionSettingsRepository;

    /**
     * @param ConnectionSettingsRepository $connectionSettingsRepository
     */
    public function __construct(ConnectionSettingsRepository $connectionSettingsRepository)
    {
        $this->connectionSettingsRepository = $connectionSettingsRepository;
    }

    /**
     * Validates connection settings.
     *
     * @param ConnectionSettings $connectionSettings
     *
     * @return void
     *
     * @throws InvalidConnectionSettingsException
     * @throws ApiCredentialsDoNotExistException
     * @throws ModeChangedException
     * @throws ConnectionSettingsNotFoundException
     * @throws MerchantDoesNotExistException
     */
    public function validateConnectionSettings(ConnectionSettings $connectionSettings): void
    {
        $existingSettings = $this->connectionSettingsRepository->getConnectionSettings();

        if (!$existingSettings) {
            return;
        }

        $apiCredentials = $existingSettings->getActiveConnectionData()->getApiCredentials();

        if ($apiCredentials === null) {
            throw new ApiCredentialsDoNotExistException(
                new TranslatableLabel('Api credential details do not exist.', 'connection.credentialsDoNotExist')
            );
        }

        if ($this->isModeChanged($connectionSettings, $existingSettings) &&
            !$this->isApiKeyChanged($connectionSettings, $existingSettings)) {
            throw new ModeChangedException(
                new TranslatableLabel('Mode changed.', 'connection.modeChanged')
            );
        }

        if ($this->isApiKeyChanged($connectionSettings, $existingSettings) &&
            !$this->isModeChanged($connectionSettings, $existingSettings)) {
            $this->validateNewApiKey($connectionSettings, $existingSettings);
        }
    }

    /**
     * Validates API key.
     *
     * @param ConnectionSettings $connectionSettings
     *
     * @return void
     *
     * @throws ApiKeyCompanyLevelException
     * @throws ConnectionSettingsNotFoundException
     * @throws InvalidApiKeyException
     * @throws UserDoesNotHaveNecessaryRolesException
     */
    public function validateApiKey(ConnectionSettings $connectionSettings): void
    {
        $proxy = $this->getProxy($connectionSettings);
        $apiCredentials = $proxy->getApiCredentialDetails();

        if (!$apiCredentials || !$apiCredentials->isActive()) {
            throw new InvalidApiKeyException(new TranslatableLabel('Api key is not valid.', 'connection.invalidKey'));
        }

        if (!$this->validateUserRoles($proxy->getUserRoles())) {
            throw new UserDoesNotHaveNecessaryRolesException(
                new TranslatableLabel('User does not have all necessary roles.', 'connection.invalidRoles')
            );
        }

        if (!$proxy->isMerchantLevelKey()) {
            throw new ApiKeyCompanyLevelException(
                new TranslatableLabel('Api key is not merchant level.', 'connection.companyLevelKey')
            );
        }

        $connectionSettings->getActiveConnectionData()->setApiCredentials($apiCredentials);
    }

    /**
     * Checks if mode has been changed.
     *
     * @param ConnectionSettings $connectionSettings
     * @param ConnectionSettings $existingSettings
     *
     * @return bool
     */
    public function isModeChanged(
        ConnectionSettings $connectionSettings,
        ConnectionSettings $existingSettings
    ): bool
    {
        return $connectionSettings->getMode() !== $existingSettings->getMode();
    }

    /**
     * Checks if API key has been changed.
     *
     * @param ConnectionSettings $connectionSettings
     * @param ConnectionSettings $existingSettings
     *
     * @return bool
     */
    public function isApiKeyChanged(ConnectionSettings $connectionSettings, ConnectionSettings $existingSettings): bool
    {
        return $connectionSettings->getActiveConnectionData()->getApiKey() !==
            $existingSettings->getActiveConnectionData()->getApiKey();
    }

    /**
     * @param ConnectionSettings $connectionSettings
     * @param ConnectionSettings $existingSettings
     *
     * @return void
     *
     * @throws ApiCredentialsDoNotExistException
     * @throws ConnectionSettingsNotFoundException
     * @throws InvalidConnectionSettingsException
     * @throws MerchantDoesNotExistException
     *
     * @noinspection NullPointerExceptionInspection
     */
    private function validateNewApiKey(ConnectionSettings $connectionSettings, ConnectionSettings $existingSettings): void
    {
        $apiCredentials = $existingSettings->getActiveConnectionData()->getApiCredentials();
        $newApiCredentials = $this->getProxy($connectionSettings)->getApiCredentialDetails();

        if ($newApiCredentials === null) {
            throw new ApiCredentialsDoNotExistException(
                new TranslatableLabel('Api credential details do not exist.', 'connection.credentialsDoNotExist')
            );
        }

        if ($newApiCredentials->getCompany() !== $apiCredentials->getCompany()) {
            throw new InvalidConnectionSettingsException(
                new TranslatableLabel(
                    'API key does not belong to the same company.',
                    'connection.apiKeyCompany'
                )
            );
        }

        $connectionSettings->getActiveConnectionData()->setApiCredentials($newApiCredentials);
        $merchants = $this->getMerchantProxy($connectionSettings)->getMerchants();

        foreach ($merchants as $merchant) {
            if ($merchant->getMerchantId() === $existingSettings->getActiveConnectionData()->getMerchantId()) {
                return;
            }
        }

        throw new MerchantDoesNotExistException(
            new TranslatableLabel(
                'Merchant account is not connected to provided credentials.',
                'connection.invalidMerchant'
            )
        );
    }

    /**
     * @param array $roles
     *
     * @return bool
     */
    private function validateUserRoles(array $roles): bool
    {
        if (empty($roles)) {
            return false;
        }

        foreach (self::NECESSARY_ROLES as $role) {
            if (!in_array($role, $roles, true)) {
                return false;
            }
        }

        return true;
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
     * @param ConnectionSettings $connectionSettings
     *
     * @return MerchantProxy
     *
     * @throws ConnectionSettingsNotFoundException
     */
    private function getMerchantProxy(ConnectionSettings $connectionSettings): MerchantProxy
    {
        return ProxyFactory::makeProxy(\Adyen\Core\BusinessLogic\AdyenAPI\Management\Merchant\Http\Proxy::class, $connectionSettings);
    }
}
