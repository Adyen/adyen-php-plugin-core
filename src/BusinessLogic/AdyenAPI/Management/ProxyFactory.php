<?php

namespace Adyen\Core\BusinessLogic\AdyenAPI\Management;

use Adyen\Core\BusinessLogic\AdyenAPI\Exceptions\ConnectionSettingsNotFoundException;
use Adyen\Core\BusinessLogic\AdyenAPI\Http\Authorized\AuthorizedProxy;
use Adyen\Core\BusinessLogic\Domain\Connection\Enums\Mode;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionSettings;
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
    public const MANAGEMENT_API_TEST_URL = 'management-test.adyen.com';
    public const MANAGEMENT_API_LIVE_URL = 'management-live.adyen.com';
    public const API_VERSION = 'v1';

    /**
     * Creates proxy object.
     *
     * @template T of AuthorizedProxy
     * @param class-string $class
     * @param ConnectionSettings|null $connectionSettings Force connection settings parameters or leve empty to use
     * saved connection settings.
     *
     * @return T
     *
     * @throws ConnectionSettingsNotFoundException
     */
    public static function makeProxy(string $class, ?ConnectionSettings $connectionSettings = null)
    {
        $connectionSettings = $connectionSettings ?: static::getConnectionSettings();

        if (!$connectionSettings) {
            throw new ConnectionSettingsNotFoundException('Connection settings not found.');
        }

        $url = self::MANAGEMENT_API_LIVE_URL;
        if ($connectionSettings->getMode() === Mode::MODE_TEST) {
            $url = self::MANAGEMENT_API_TEST_URL;
        }

        return new $class(
            static::getHttpClient(),
            $url,
            self::API_VERSION,
            $connectionSettings->getActiveConnectionData()->getApiKey()
        );
    }

    /**
     * @return ConnectionSettings|null
     */
    protected static function getConnectionSettings(): ?ConnectionSettings
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
