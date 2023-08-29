<?php
/** @noinspection PhpDuplicateArrayKeysInspection */

namespace Adyen\Core\Tests\Infrastructure\TaskExecution;

use Adyen\Core\Infrastructure\Configuration\ConfigEntity;
use Adyen\Core\Infrastructure\Configuration\Configuration;
use Adyen\Core\Infrastructure\Configuration\ConfigurationManager;
use Adyen\Core\Infrastructure\Http\HttpClient;
use Adyen\Core\Infrastructure\Logger\Interfaces\DefaultLoggerAdapter;
use Adyen\Core\Infrastructure\Logger\Interfaces\ShopLoggerAdapter;
use Adyen\Core\Infrastructure\Logger\Logger;
use Adyen\Core\Infrastructure\ORM\RepositoryRegistry;
use Adyen\Core\Infrastructure\Serializer\Concrete\NativeSerializer;
use Adyen\Core\Infrastructure\Serializer\Serializer;
use Adyen\Core\Infrastructure\TaskExecution\Events\QueueItemStateTransitionEventBus;
use Adyen\Core\Infrastructure\TaskExecution\Interfaces\TaskRunnerWakeup;
use Adyen\Core\Infrastructure\TaskExecution\QueueItem;
use Adyen\Core\Infrastructure\TaskExecution\QueueItemStarter;
use Adyen\Core\Infrastructure\TaskExecution\QueueService;
use Adyen\Core\Infrastructure\Utility\Events\EventBus;
use Adyen\Core\Infrastructure\Utility\TimeProvider;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\Logger\TestDefaultLogger;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\Logger\TestShopLogger;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\ORM\MemoryQueueItemRepository;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\ORM\MemoryRepository;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\FooTask;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\TestQueueService;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\TestTaskRunnerWakeupService;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\TestConfigurationManager;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\TestHttpClient;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\TestShopConfiguration;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\Utility\TestTimeProvider;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;
use PHPUnit\Framework\TestCase;

/**
 * Class QueueItemStarterTest
 *
 * @package Adyen\Core\Tests\Infrastructure\TaskExecution
 */
class QueueItemStarterTest extends TestCase
{
    /** @var \Adyen\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\TestQueueService */
    public $queue;
    /** @var MemoryQueueItemRepository */
    public $queueStorage;
    /** @var TestTimeProvider */
    public $timeProvider;
    /** @var \Adyen\Core\Tests\Infrastructure\Common\TestComponents\Logger\TestShopLogger */
    public $logger;
    /** @var Configuration */
    public $shopConfiguration;
    /** @var ConfigurationManager */
    public $configurationManager;

    /**
     * @throws \Exception
     */
    public function setUp(): void
    {
        RepositoryRegistry::registerRepository(QueueItem::CLASS_NAME, MemoryQueueItemRepository::getClassName());
        RepositoryRegistry::registerRepository(ConfigEntity::CLASS_NAME, MemoryRepository::getClassName());

        $timeProvider = new TestTimeProvider();
        $queue = new TestQueueService();
        $shopLogger = new TestShopLogger();
        $configurationManager = new TestConfigurationManager();
        $shopConfiguration = new TestShopConfiguration();
        $serializer = new NativeSerializer();

        new TestServiceRegister(
            array(
                ConfigurationManager::CLASS_NAME => function () use ($configurationManager) {
                    return $configurationManager;
                },
                TimeProvider::CLASS_NAME => function () use ($timeProvider) {
                    return $timeProvider;
                },
                TaskRunnerWakeup::CLASS_NAME => function () {
                    return new TestTaskRunnerWakeupService();
                },
                QueueService::CLASS_NAME => function () use ($queue) {
                    return $queue;
                },
                EventBus::CLASS_NAME => function () {
                    return EventBus::getInstance();
                },
                DefaultLoggerAdapter::CLASS_NAME => function () {
                    return new TestDefaultLogger();
                },
                ShopLoggerAdapter::CLASS_NAME => function () use ($shopLogger) {
                    return $shopLogger;
                },
                Configuration::CLASS_NAME => function () use ($shopConfiguration) {
                    return $shopConfiguration;
                },
                HttpClient::CLASS_NAME => function () {
                    return new TestHttpClient();
                },
                Serializer::CLASS_NAME => function () use ($serializer) {
                    return $serializer;
                },
                QueueItemStateTransitionEventBus::CLASS_NAME => function () {
                    return QueueItemStateTransitionEventBus::getInstance();
                },
            )
        );


        // Initialize logger component with new set of log adapters
        Logger::resetInstance();

        $shopConfiguration->setIntegrationName('Shop1');

        $this->queueStorage = RepositoryRegistry::getQueueItemRepository();
        $this->timeProvider = $timeProvider;
        $this->queue = $queue;
        $this->logger = $shopLogger;
        $this->shopConfiguration = $shopConfiguration;
        $this->configurationManager = $configurationManager;
    }

    /**
     * @throws \Adyen\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     */
    public function testRunningItemStarter()
    {
        // Arrange
        $queueItem = $this->queue->enqueue(
            'test',
            new FooTask()
        );
        $itemStarter = new QueueItemStarter($queueItem->getId());

        // Act
        $itemStarter->run();

        // Assert
        $findCallHistory = $this->queue->getMethodCallHistory('find');
        $startCallHistory = $this->queue->getMethodCallHistory('start');
        self::assertCount(1, $findCallHistory);
        self::assertCount(1, $startCallHistory);
        self::assertEquals($queueItem->getId(), $findCallHistory[0]['id']);
        /** @var QueueItem $startedQueueItem */
        $startedQueueItem = $startCallHistory[0]['queueItem'];
        self::assertEquals($queueItem->getId(), $startedQueueItem->getId());
    }

    /**
     * @throws \Adyen\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     */
    public function testItemStarterMustBeRunnableAfterDeserialization()
    {
        // Arrange
        $queueItem = $this->queue->enqueue(
            'test',
            new FooTask()
        );
        $itemStarter = new QueueItemStarter($queueItem->getId());
        /** @var QueueItemStarter $unserializedItemStarter */
        $unserializedItemStarter = Serializer::unserialize(Serializer::serialize($itemStarter));

        // Act
        $unserializedItemStarter->run();

        // Assert
        $findCallHistory = $this->queue->getMethodCallHistory('find');
        $startCallHistory = $this->queue->getMethodCallHistory('start');
        self::assertCount(1, $findCallHistory);
        self::assertCount(1, $startCallHistory);
        self::assertEquals($queueItem->getId(), $findCallHistory[0]['id']);
        /** @var QueueItem $startedQueueItem */
        $startedQueueItem = $startCallHistory[0]['queueItem'];
        self::assertEquals($queueItem->getId(), $startedQueueItem->getId());
    }

    /**
     * @throws \Adyen\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     */
    public function testItemsStarterMustSetTaskExecutionContextInConfiguration()
    {
        // Arrange
        $queueItem = $this->queue->enqueue('test', new FooTask(), 'test');
        $itemStarter = new QueueItemStarter($queueItem->getId());

        // Act
        $itemStarter->run();

        // Assert
        self::assertSame(
            'test',
            $this->configurationManager->getContext(),
            'Item starter must set task context before task execution.'
        );
    }
}
