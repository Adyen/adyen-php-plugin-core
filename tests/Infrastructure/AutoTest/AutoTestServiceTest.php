<?php

namespace Adyen\Core\Tests\Infrastructure\AutoTest;

use Adyen\Core\Infrastructure\AutoTest\AutoTestLogger;
use Adyen\Core\Infrastructure\AutoTest\AutoTestService;
use Adyen\Core\Infrastructure\Exceptions\StorageNotAccessibleException;
use Adyen\Core\Infrastructure\Http\DTO\Options;
use Adyen\Core\Infrastructure\Http\HttpClient;
use Adyen\Core\Infrastructure\Logger\Interfaces\ShopLoggerAdapter;
use Adyen\Core\Infrastructure\Logger\LogData;
use Adyen\Core\Infrastructure\Logger\Logger;
use Adyen\Core\Infrastructure\ORM\RepositoryRegistry;
use Adyen\Core\Infrastructure\ServiceRegister;
use Adyen\Core\Infrastructure\TaskExecution\Events\QueueItemStateTransitionEventBus;
use Adyen\Core\Infrastructure\TaskExecution\QueueItem;
use Adyen\Core\Infrastructure\TaskExecution\QueueService;
use Adyen\Core\Infrastructure\TaskExecution\TaskRunnerWakeupService;
use Adyen\Core\Tests\Infrastructure\Common\BaseInfrastructureTestWithServices;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\ORM\MemoryQueueItemRepository;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\ORM\MemoryRepository;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\TestQueueService;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\TestTaskRunnerWakeupService;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\TestHttpClient;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class AutoTestServiceTest.
 *
 * @package Adyen\Core\Tests\Infrastructure\AutoTest
 */
class AutoTestServiceTest extends BaseInfrastructureTestWithServices
{
    /**
     * @var TestHttpClient
     */
    protected $httpClient;
    /**
     * @var TestHttpClient
     */
    protected $logger;

    /**
     * @throws \Exception
     */
    public function setUp(): void
    {
        parent::setUp();

        RepositoryRegistry::registerRepository(QueueItem::CLASS_NAME, MemoryQueueItemRepository::getClassName());

        $me = $this;
        $this->httpClient = new TestHttpClient();
        TestServiceRegister::registerService(
            HttpClient::CLASS_NAME,
            function () use ($me) {
                return $me->httpClient;
            }
        );

        $queue = new TestQueueService();
        TestServiceRegister::registerService(
            QueueService::CLASS_NAME,
            function () use ($queue) {
                return $queue;
            }
        );

        $wakeupService = new TestTaskRunnerWakeupService();
        TestServiceRegister::registerService(
            TaskRunnerWakeupService::CLASS_NAME,
            function () use ($wakeupService) {
                return $wakeupService;
            }
        );
        TestServiceRegister::registerService(
            QueueItemStateTransitionEventBus::CLASS_NAME,
            function () {
                return QueueItemStateTransitionEventBus::getInstance();
            }
        );
    }

    /**
     * @inheritDoc
     */
    public function tearDown(): void
    {
        parent::tearDown();

        TestRepositoryRegistry::cleanUp();
        AutoTestLogger::resetInstance();
    }

    /**
     * Test setting auto-test mode.
     */
    public function testSetAutoTestMode()
    {
        RepositoryRegistry::registerRepository(LogData::getClassName(), MemoryRepository::getClassName());
        $service = new AutoTestService();
        $service->setAutoTestMode(true);

        $repo = RepositoryRegistry::getRepository(LogData::getClassName());
        self::assertNotNull($repo, 'Log repository should be registered.');

        $loggerService = ServiceRegister::getService(ShopLoggerAdapter::CLASS_NAME);
        self::assertNotNull($loggerService, 'Logger service should be registered.');
        self::assertInstanceOf(
            AutoTestLogger::class,
            $loggerService,
            'AutoTestLogger service should be registered.'
        );

        self::assertTrue($this->shopConfig->isAutoTestMode(), 'Auto-test mode should be set.');
    }

    /**
     * Test successful start of the auto-test.
     */
    public function testStartAutoTestSuccess()
    {
        RepositoryRegistry::registerRepository(LogData::getClassName(), MemoryRepository::getClassName());
        $domain = parse_url($this->shopConfig->getAsyncProcessUrl(''), PHP_URL_HOST);
        $this->shopConfig->setHttpConfigurationOptions($domain, array(new Options('test', 'value')));

        $service = new AutoTestService();
        $queueItemId = $service->startAutoTest();

        self::assertNotNull($queueItemId, 'Test task should be enqueued.');

        $status = $service->getAutoTestTaskStatus($queueItemId);
        self::assertEquals('queued', $status->taskStatus, 'AutoTest tasks should be enqueued.');
        $logger = $this->shopLogger;
        $service->stopAutoTestMode(
            function () use ($logger) {
                return $logger;
            }
        );
        // starting auto-test should produce 2 logs. Additional logs should not be added to the auto-test logs.
        Logger::logInfo('this should not be added to the log');

        $allLogs = AutoTestLogger::getInstance()->getLogs();
        $allLogsArray = AutoTestLogger::getInstance()->getLogsArray();
        self::assertNotEmpty($allLogs, 'Starting logs should be added.');
        self::assertCount(2, $allLogs, 'Additional logs should not be added.');
        self::assertCount(count($allLogs), $allLogsArray, 'ToArray should produce the same number of items.');
        self::assertEquals('Start auto-test', $allLogs[0]->getMessage(), 'Starting logs should be added.');

        $context = $allLogs[1]->getContext();
        self::assertCount(1, $context, 'Current HTTP configuration options should be logged.');
        self::assertEquals($domain, $context[0]->getName(), 'Current HTTP configuration options should be logged.');

        $options = $context[0]->getValue();
        self::assertArrayHasKey(
            'HTTPOptions',
            $options,
            'Current HTTP configuration options should be logged.'
        );

        self::assertCount(1, $options, 'One HTTP configuration options should be set.');
    }

    /**
     * Tests failure when storage is not available.
     */
    public function testStartAutoTestStorageFailure()
    {
        $this->expectException(StorageNotAccessibleException::class);

        // repository is not registered
        $service = new AutoTestService();
        $service->startAutoTest();
    }
}
