<?php

namespace Adyen\Core\Tests\BusinessLogic\AdminAPI\InfoSettings;

use Adyen\Core\BusinessLogic\AdminAPI\AdminAPI;
use Adyen\Core\BusinessLogic\Bootstrap\SingleInstance;
use Adyen\Core\Infrastructure\Configuration\Configuration;
use Adyen\Core\Infrastructure\Configuration\ConfigurationManager;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\TestConfigurationManager;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\TestShopConfiguration;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;
use Exception;

/**
 * Class DebugApiTest
 *
 * @package Adyen\Core\Tests\BusinessLogic\AdminAPI\InfoSettings
 */
class DebugApiTest extends BaseTestCase
{
    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->configuration = new TestShopConfiguration();

        TestServiceRegister::registerService(
            Configuration::class,
            new SingleInstance(function () {
                return $this->configuration;
            })
        );

        TestServiceRegister::registerService(
            ConfigurationManager::class,
            new SingleInstance(function () {
                return new TestConfigurationManager();
            })
        );
    }

    /**
     * @throws Exception
     */
    public function testGetDebugModeSuccessful(): void
    {
        // Act
        $state = AdminAPI::get()->debug()->getDebugMode();

        // Assert
        self::assertTrue($state->isSuccessful());
    }

    /**
     * @throws Exception
     */
    public function testGetDebugModeToArray(): void
    {
        // Act
        $state = AdminAPI::get()->debug()->getDebugMode();

        // Assert
        self::assertEquals(['debugMode' => false], $state->toArray());
        self::assertFalse($this->configuration->isDebugModeEnabled());
    }

    /**
     * @throws Exception
     */
    public function testSetDebugModeSuccessful(): void
    {
        // Act
        $state = AdminAPI::get()->debug()->setDebugMode(true);

        // Assert
        self::assertTrue($state->isSuccessful());
    }

    /**
     * @throws Exception
     */
    public function testSetDebugModeSuccessfulToArray(): void
    {
        // Act
        $state = AdminAPI::get()->debug()->setDebugMode(true);

        // Assert
        self::assertEquals(['success' => true], $state->toArray());
    }

    /**
     * @throws Exception
     */
    public function testSetDebugMode(): void
    {
        // Act
        AdminAPI::get()->debug()->setDebugMode(true);

        // Assert
        self::assertTrue($this->configuration->isDebugModeEnabled());
    }
}
