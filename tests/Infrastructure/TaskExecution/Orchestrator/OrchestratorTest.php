<?php

namespace Adyen\Core\Tests\Infrastructure\TaskExecution\Orchestrator;

use Adyen\Core\Infrastructure\Configuration\ConfigurationManager;
use Adyen\Core\Infrastructure\Logger\Interfaces\ShopLoggerAdapter;
use Adyen\Core\Infrastructure\ORM\RepositoryRegistry;
use Adyen\Core\Infrastructure\Serializer\Concrete\JsonSerializer;
use Adyen\Core\Infrastructure\Serializer\Concrete\NativeSerializer;
use Adyen\Core\Infrastructure\Serializer\Serializer;
use Adyen\Core\Infrastructure\ServiceRegister;
use Adyen\Core\Infrastructure\TaskExecution\Composite\ExecutionDetails;
use Adyen\Core\Infrastructure\TaskExecution\Events\QueueItemStateTransitionEventBus;
use Adyen\Core\Infrastructure\TaskExecution\Interfaces\TaskRunnerWakeup;
use Adyen\Core\Infrastructure\TaskExecution\QueueItem;
use Adyen\Core\Infrastructure\TaskExecution\QueueService;
use Adyen\Core\Infrastructure\TaskExecution\TaskRunnerWakeupService;
use Adyen\Core\Infrastructure\Utility\Events\EventBus;
use Adyen\Core\Infrastructure\Utility\TimeProvider;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\Logger\TestShopLogger;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\ORM\MemoryQueueItemRepository;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\FooOrchestrator;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\TestQueueService;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\TestConfigurationManager;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\Utility\TestTimeProvider;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;
use PHPUnit\Framework\TestCase;

class OrchestratorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        TestServiceRegister::registerService(QueueService::CLASS_NAME, static function () {
            return new TestQueueService();
        });
        TestServiceRegister::registerService(ConfigurationManager::CLASS_NAME, static function () {
            return new TestConfigurationManager();
        });
        TestServiceRegister::registerService(TimeProvider::CLASS_NAME, static function () {
            return new TestTimeProvider();
        });
        TestServiceRegister::registerService(EventBus::CLASS_NAME, static function () {
            return EventBus::getInstance();
        });
        TestServiceRegister::registerService(QueueItemStateTransitionEventBus::CLASS_NAME, static function () {
            return QueueItemStateTransitionEventBus::getInstance();
        });
        TestServiceRegister::registerService(TaskRunnerWakeup::CLASS_NAME, static function() {return new TaskRunnerWakeupService();});
        TestServiceRegister::registerService(ShopLoggerAdapter::CLASS_NAME, static function() {return new TestShopLogger();});
        RepositoryRegistry::registerRepository(QueueItem::CLASS_NAME, MemoryQueueItemRepository::getClassName());
    }

    public function testSerializeJsonSerializer()
    {
        // Arrange
        $task = new FooOrchestrator();
        $task->taskList = [new ExecutionDetails(12, 3), new ExecutionDetails(7, 15)];
        TestServiceRegister::registerService(Serializer::CLASS_NAME, function () {
            return new JsonSerializer();
        });
        $serialized = Serializer::serialize($task);

        // Act
        $unserialized = Serializer::unserialize($serialized);

        // Assert
        self::assertEquals($task, $unserialized);
    }

    public function testSerializeNativeSerializer()
    {
        // Arrange
        $task = new FooOrchestrator();
        $task->taskList = [new ExecutionDetails(12, 3), new ExecutionDetails(7, 15)];
        TestServiceRegister::registerService(Serializer::CLASS_NAME, function () {
            return new NativeSerializer();
        });
        $serialized = Serializer::serialize($task);

        // Act
        $unserialized = Serializer::unserialize($serialized);

        // Assert
        self::assertEquals($task, $unserialized);
    }

    public function testExecuteSubJobsCreated()
    {
        // Arrange
        $task = new FooOrchestrator();

        // Act
        $task->execute();

        // Assert
        self::assertCount(FooOrchestrator::SUB_JOB_COUNT, $task->taskList);
    }

    public function testExecuteSubJobsStatusSet()
    {
        // Arrange
        $task = new FooOrchestrator();

        // Act
        $task->execute();

        // Assert
        foreach ($task->taskList as $details) {
            $job = ServiceRegister::getService(QueueService::CLASS_NAME)->find($details->getExecutionId());
            self::assertEquals(QueueItem::QUEUED, $job->getStatus());
        }
    }

    public function testUpdateProgressInvalidProgress()
    {
        $this->expectException(\InvalidArgumentException::class);

        // Arrange
        $task = new FooOrchestrator();

        // Act
        $task->updateSubJobProgress(1, 150);
    }

    public function testAbortSubJobsAborted()
    {
        // Arrange
        $queue = ServiceRegister::getService(QueueService::CLASS_NAME);
        $item = $queue->enqueue('test', new FooOrchestrator());
        $queue->start($item);

        // Act
        $item->getTask()->onAbort();

        // Assert
        foreach ($item->getTask()->taskList as $details) {
            $job = ServiceRegister::getService(QueueService::CLASS_NAME)->find($details->getExecutionId());
            self::assertEquals(QueueItem::ABORTED, $job->getStatus());
        }
    }

    public function testFailSubJobsAborted()
    {
        // Arrange
        $queue = ServiceRegister::getService(QueueService::CLASS_NAME);
        $item = $queue->enqueue('test', new FooOrchestrator());
        $queue->start($item);

        // Act
        $item->getTask()->onFail();

        // Assert
        foreach ($item->getTask()->taskList as $details) {
            $job = ServiceRegister::getService(QueueService::CLASS_NAME)->find($details->getExecutionId());
            self::assertEquals(QueueItem::ABORTED, $job->getStatus());
        }
    }
}
