<?php

namespace Adyen\Core\BusinessLogic\AdyenAPI\Checkout;

use Adyen\Core\BusinessLogic\AdyenAPI\Exceptions\ClientPrefixDoesNotExistException as APIClientPrefixDoesNotExistException;
use Adyen\Core\BusinessLogic\AdyenAPI\Exceptions\ConnectionSettingsNotFoundException;
use Adyen\Core\BusinessLogic\AdyenAPI\Http\Authorized\AuthorizedProxy;
use Adyen\Core\BusinessLogic\Domain\Connection\Enums\Mode;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionSettings as ConnectionSettingsEntity;
use Adyen\Core\BusinessLogic\Domain\Connection\Repositories\ConnectionSettingsRepository;
use Adyen\Core\Infrastructure\Http\HttpClient;
use Adyen\Core\Infrastructure\ServiceRegister;

/**
 * Class ProxyFactory
 *
 * @package Adyen\Core\BusinessLogic\AdyenAPI\MerchantAPI
 */
class ProxyFactory
{
    private const CHECKOUT_API_TEST_URL = 'checkout-test.adyen.com';
    private const CHECKOUT_API_LIVE_URL = 'checkout-live.adyenpayments.com/checkout';
    private const API_VERSION = 'v69';

    /**
     * Creates proxy object.
     *
     * @template T of AuthorizedProxy
     * @param class-string $class
     *
     * @return T
     *
     * @throws ConnectionSettingsNotFoundException
     * @throws APIClientPrefixDoesNotExistException
     */
    public static function makeProxy(string $class)
    {
        $connectionSettings = static::getConnectionSettings();

        if (!$connectionSettings) {
            throw new ConnectionSettingsNotFoundException('Connection settings not found.');
        }

        $url = self::CHECKOUT_API_TEST_URL;
        if ($connectionSettings->getMode() === Mode::MODE_LIVE) {
            $clientPrefix = $connectionSettings->getLiveData()->getClientPrefix();

            if (!$clientPrefix) {
                throw new APIClientPrefixDoesNotExistException('Client key not found.');
            }

            $url = $clientPrefix . '-' . self::CHECKOUT_API_LIVE_URL;
        }

        return new $class(
            static::getHttpClient(),
            $url,
            self::API_VERSION,
            $connectionSettings->getActiveConnectionData()->getApiKey()
        );
    }

    /**
     * @return ConnectionSettingsEntity|null
     */
    protected static function getConnectionSettings(): ?ConnectionSettingsEntity
    {
        return static::getConnectionSettingsRepository()->getConnectionSettings();
    }

    /**
     * @return ConnectionSettingsRepository
     */
    protected static function getConnectionSettingsRepository(): ConnectionSettingsRepository
    {
        return ServiceRegister::getService(ConnectionSettingsRepository::class);
    }

    /**
     * @return HttpClient
     */
    protected static function getHttpClient(): HttpClient
    {
        return ServiceRegister::getService(HttpClient::class);
    }
}
