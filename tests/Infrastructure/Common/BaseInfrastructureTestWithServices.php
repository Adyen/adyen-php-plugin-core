<?php
/** @noinspection PhpDuplicateArrayKeysInspection */

namespace Adyen\Core\Tests\Infrastructure\Common;

use Adyen\Core\Infrastructure\Configuration\ConfigEntity;
use Adyen\Core\Infrastructure\Configuration\Configuration;
use Adyen\Core\Infrastructure\Configuration\ConfigurationManager;
use Adyen\Core\Infrastructure\Logger\Interfaces\DefaultLoggerAdapter;
use Adyen\Core\Infrastructure\Logger\Interfaces\ShopLoggerAdapter;
use Adyen\Core\Infrastructure\Logger\Logger;
use Adyen\Core\Infrastructure\Logger\LoggerConfiguration;
use Adyen\Core\Infrastructure\ORM\RepositoryRegistry;
use Adyen\Core\Infrastructure\Serializer\Concrete\NativeSerializer;
use Adyen\Core\Infrastructure\Serializer\Serializer;
use Adyen\Core\Infrastructure\Utility\Events\EventBus;
use Adyen\Core\Infrastructure\Utility\TimeProvider;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\Logger\TestDefaultLogger;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\Logger\TestShopLogger;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\ORM\MemoryRepository;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\ORM\MemoryStorage;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\TestConfigurationManager;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\TestShopConfiguration;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\Utility\TestTimeProvider;
use DateTime;
use PHPUnit\Framework\TestCase;

/**
 * Class BaseTest.
 *
 * @package Adyen\Core\Tests\Infrastructure\Common
 */
abstract class BaseInfrastructureTestWithServices extends TestCase
{
    /**
     * @var TestShopConfiguration
     */
    public $shopConfig;
    /**
     * @var TestShopLogger
     */
    public $shopLogger;
    /**
     * @var TestTimeProvider
     */
    public $timeProvider;
    /**
     * @var TestDefaultLogger
     */
    public $defaultLogger;
    /**
     * @var array
     */
    public $eventHistory;
    /**
     * @var \Adyen\Core\Infrastructure\Serializer\Serializer
     */
    public $serializer;

    /**
     * @throws \Exception
     */
    protected function setUp(): void
    {
        RepositoryRegistry::registerRepository(ConfigEntity::CLASS_NAME, MemoryRepository::getClassName());

        $me = $this;

        $this->timeProvider = new TestTimeProvider();
        $this->timeProvider->setCurrentLocalTime(new DateTime());
        $this->shopConfig = new TestShopConfiguration();
        $this->shopLogger = new TestShopLogger();
        $this->defaultLogger = new TestDefaultLogger();
        $this->serializer = new NativeSerializer();

        new TestServiceRegister(
            array(
                ConfigurationManager::CLASS_NAME => function () {
                    return new TestConfigurationManager();
                },
                Configuration::CLASS_NAME => function () use ($me) {
                    return $me->shopConfig;
                },
                TimeProvider::CLASS_NAME => function () use ($me) {
                    return $me->timeProvider;
                },
                DefaultLoggerAdapter::CLASS_NAME => function () use ($me) {
                    return $me->defaultLogger;
                },
                ShopLoggerAdapter::CLASS_NAME => function () use ($me) {
                    return $me->shopLogger;
                },
                EventBus::CLASS_NAME => function () {
                    return EventBus::getInstance();
                },
                Serializer::CLASS_NAME => function () use ($me) {
                    return $me->serializer;
                },
            )
        );
    }

    protected function tearDown(): void
    {
        Logger::resetInstance();
        LoggerConfiguration::resetInstance();
        MemoryStorage::reset();
        TestRepositoryRegistry::cleanUp();

        parent::tearDown();
    }
}
