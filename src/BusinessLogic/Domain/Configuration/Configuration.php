<?php

/** @noinspection PhpDocMissingThrowsInspection */

/** @noinspection PhpUnusedParameterInspection */

namespace Adyen\Core\BusinessLogic\Domain\Configuration;

use Adyen\Core\Infrastructure\Configuration\Configuration as BaseConfiguration;

/**
 * Class Configuration.
 *
 * @package Adyen\Core\Infrastructure\Configuration
 */
abstract class Configuration extends BaseConfiguration
{
    /**
     * Singleton instance of this class.
     *
     * @var static
     */
    protected static $instance;

    /**
     * Retrieves integration version.
     *
     * @return string Integration version.
     */
    abstract public function getIntegrationVersion();

    /**
     * Gets the current plugin name (e.g. AdyenShopify)
     * @return string
     */
    abstract public function getPluginName();

    /**
     * Gets the current plugin version (e.g. 1.2.5)
     * @return string
     */
    abstract public function getPluginVersion();
}
