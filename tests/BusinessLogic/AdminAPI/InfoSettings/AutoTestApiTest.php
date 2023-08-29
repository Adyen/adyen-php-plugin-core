<?php

namespace Adyen\Core\Tests\BusinessLogic\AdminAPI\InfoSettings;

use Adyen\Core\BusinessLogic\AdminAPI\AdminAPI;
use Adyen\Core\Infrastructure\AutoTest\AutoTestLogger;
use Adyen\Core\Infrastructure\AutoTest\AutoTestService;
use Adyen\Core\Infrastructure\AutoTest\AutoTestStatus;
use Adyen\Core\Infrastructure\Exceptions\StorageNotAccessibleException;
use Adyen\Core\Infrastructure\Logger\LogData;
use Adyen\Core\Infrastructure\Logger\Logger;
use Adyen\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use Adyen\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use Adyen\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use Adyen\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;
use Adyen\Core\Infrastructure\TaskExecution\QueueItem;
use Adyen\Core\Tests\BusinessLogic\AdminAPI\InfoSettings\Mocks\MockAutoTestService;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\ORM\MemoryRepository;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class AutoTestApiTest
 *
 * @package Adyen\Core\Tests\BusinessLogic\AdminAPI\InfoSettings
 */
class AutoTestApiTest extends BaseTestCase
{
    /**
     * @var MockAutoTestService
     */
    public $service;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new MockAutoTestService();

        TestServiceRegister::registerService(
            AutoTestService::class,
            function () {
                return $this->service;
            }
        );

        TestRepositoryRegistry::registerRepository(LogData::getClassName(), MemoryRepository::getClassName());
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
     * @throws StorageNotAccessibleException
     * @throws QueueStorageUnavailableException
     */
    public function testStartAutoTestSuccessful(): void
    {
        // act
        $result = AdminAPI::get()->autoTest()->startAutoTest();

        // assert
        self::assertTrue($result->isSuccessful());
    }

    /**
     * @throws StorageNotAccessibleException
     * @throws QueueStorageUnavailableException
     */
    public function testStartAutoTestToArray(): void
    {
        // act
        $result = AdminAPI::get()->autoTest()->startAutoTest();

        // assert
        self::assertEquals(['queueItemId' => $this->service->startAutoTestResult], $result->toArray());
    }

    /**
     * @throws StorageNotAccessibleException
     * @throws QueueStorageUnavailableException
     */
    public function testStartMethodCall(): void
    {
        // act
        AdminAPI::get()->autoTest()->startAutoTest();

        // assert
        self::assertEquals(['startAutoTest'], $this->service->callHistory);
    }

    /**
     * @throws StorageNotAccessibleException
     * @throws QueueStorageUnavailableException
     */
    public function testStartFailed(): void
    {
        // arrange
        $this->service->shouldFail = true;

        // act
        $result = AdminAPI::get()->autoTest()->startAutoTest();

        // assert
        $this->assertFalse($result->isSuccessful());
    }

    /**
     * @return void
     *
     * @throws QueryFilterInvalidParamException
     * @throws RepositoryClassException
     * @throws RepositoryNotRegisteredException
     */
    public function testCheckStatusSuccessful(): void
    {
        // arrange
        $this->service->getAutoTestTaskStatusResult = new AutoTestStatus('test', true, 'Test', []);

        // act
        $result = AdminAPI::get()->autoTest()->autoTestStatus(1);

        // assert
        self::assertTrue($result->isSuccessful());
    }

    /**
     * @return void
     *
     * @throws QueryFilterInvalidParamException
     * @throws RepositoryClassException
     * @throws RepositoryNotRegisteredException
     */
    public function testCheckStatusToArraySuccess(): void
    {
        // arrange
        $this->service->getAutoTestTaskStatusResult = new AutoTestStatus(QueueItem::COMPLETED, true, 'Test', []);
        $expected = [
            'finished' => true,
            'status' => true,
            'message' => 'auto.test.success'
        ];

        // act
        $result = AdminAPI::get()->autoTest()->autoTestStatus(1);

        // assert
        self::assertEquals($expected, $result->toArray());
    }

    /**
     * @return void
     *
     * @throws QueryFilterInvalidParamException
     * @throws RepositoryClassException
     * @throws RepositoryNotRegisteredException
     */
    public function testCheckStatusToArrayFail(): void
    {
        // arrange
        $this->service->getAutoTestTaskStatusResult = new AutoTestStatus(QueueItem::FAILED, true, 'Test', []);
        $expected = [
            'finished' => true,
            'status' => false,
            'message' => 'auto.test.fail'
        ];

        // act
        $result = AdminAPI::get()->autoTest()->autoTestStatus(1);

        // assert
        self::assertEquals($expected, $result->toArray());
    }

    /**
     * @throws QueryFilterInvalidParamException
     * @throws RepositoryClassException
     * @throws RepositoryNotRegisteredException
     */
    public function testCheckStatusMethodCall(): void
    {
        // arrange
        $this->service->getAutoTestTaskStatusResult = new AutoTestStatus('test', false, 'Test', []);

        // act
        AdminAPI::get()->autoTest()->autoTestStatus(1);

        // assert
        self::assertEquals(['getAutoTestTaskStatus'], $this->service->callHistory);
    }

    /**
     * @throws QueryFilterInvalidParamException
     * @throws RepositoryClassException
     * @throws RepositoryNotRegisteredException
     */
    public function testCheckStatusMethodCallFinished(): void
    {
        // arrange
        $this->service->getAutoTestTaskStatusResult = new AutoTestStatus('test', true, 'Test', []);

        // act
        AdminAPI::get()->autoTest()->autoTestStatus(1);

        // assert
        self::assertEquals(['getAutoTestTaskStatus', 'stopAutoTestMode'], $this->service->callHistory);
    }

    /**
     * @return void
     * @throws RepositoryNotRegisteredException
     */
    public function testAutoTestReportResponseSuccessful(): void
    {
        // arrange
        $this->service->getAutoTestTaskStatusResult = new AutoTestStatus('test', true, 'Test', []);
        AutoTestLogger::getInstance()->logMessage(
            new LogData(
                'integration1',
                Logger::ERROR,
                0,
                '',
                $message = 'error1'
            )
        );

        AutoTestLogger::getInstance()->logMessage(
            new LogData(
                'integration2',
                Logger::ERROR,
                0,
                '',
                $message = 'error2'
            )
        );

        // act
        $logs = AdminAPI::get()->autoTest()->autoTestReport();

        // assert
        self::assertTrue($logs->isSuccessful());
    }

    /**
     * @return void
     * @throws RepositoryNotRegisteredException
     */
    public function testAutoTestReportResponse(): void
    {
        // arrange
        $this->service->getAutoTestTaskStatusResult = new AutoTestStatus('test', true, 'Test', []);
        AutoTestLogger::getInstance()->logMessage(
            new LogData(
                'integration1',
                Logger::ERROR,
                0,
                '',
                $message = 'error1'
            )
        );

        AutoTestLogger::getInstance()->logMessage(
            new LogData(
                'integration2',
                Logger::ERROR,
                0,
                '',
                $message = 'error2'
            )
        );

        // act
        $logs = AdminAPI::get()->autoTest()->autoTestReport();

        // assert
        self::assertCount(2, $logs->toArray());
    }
}
