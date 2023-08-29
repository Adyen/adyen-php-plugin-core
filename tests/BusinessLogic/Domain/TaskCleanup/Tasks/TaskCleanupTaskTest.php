<?php

namespace Adyen\Core\Tests\BusinessLogic\Domain\TaskCleanup\Tasks;

use Adyen\Core\BusinessLogic\Domain\TaskCleanup\Interfaces\TaskCleanupRepository;
use Adyen\Core\BusinessLogic\Domain\TaskCleanup\Tasks\TaskCleanupTask;
use Adyen\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use Adyen\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use Adyen\Core\Infrastructure\TaskExecution\Exceptions\AbortTaskExecutionException;
use Adyen\Core\Infrastructure\TaskExecution\QueueItem;
use Adyen\Core\Infrastructure\TaskExecution\QueueService;
use Adyen\Core\Infrastructure\Utility\TimeProvider;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;
use DateInterval;
use DateTime;

/**
 * Class TaskCleanupTaskTest
 *
 * @package Adyen\Core\Tests\BusinessLogic\Domain\TaskCleanup\Tasks
 */
class TaskCleanupTaskTest extends BaseTestCase
{
    /**
     * @var TaskCleanupTask
     */
    public $task;

    /**
     * @var TimeProvider
     */
    public $timeProvider;

    /**
     * @var QueueService
     */
    public $queueService;

    /**
     * @var RepositoryInterface
     */
    public $repository;

    /**
     * @var TaskCleanupRepository
     */
    public $taskCleanupRepository;


    /**
     * @return void
     *
     * @throws RepositoryNotRegisteredException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->timeProvider = TestServiceRegister::getService(TimeProvider::CLASS_NAME);
        $this->queueService = TestServiceRegister::getService(QueueService::CLASS_NAME);
        $this->repository = TestRepositoryRegistry::getRepository(QueueItem::getClassName());
        $this->taskCleanupRepository = TestServiceRegister::getService(TaskCleanupRepository::class);
        $this->task = new TaskCleanupTask();
    }

    /**
     * @return void
     *
     * @throws AbortTaskExecutionException
     */
    public function testCleaningCompletedTasks(): void
    {
        // Arrange
        $item1 = new QueueItem();
        $item1->setStatus(QueueItem::COMPLETED);
        $item2 = new QueueItem();
        $item2->setStatus(QueueItem::COMPLETED);
        $item3 = new QueueItem();
        $item3->setStatus(QueueItem::COMPLETED);
        $item4 = new QueueItem();
        $item4->setStatus(QueueItem::COMPLETED);
        $this->repository->save($item1);
        $this->repository->save($item2);
        $this->repository->save($item3);
        $this->repository->save($item4);

        // Act
        $cleanupTask = new TaskCleanupTask();
        $cleanupTask->execute();

        // Assert
        self::assertEquals(0, $this->taskCleanupRepository->getCompletedCount());
        self::assertEquals(0, $this->taskCleanupRepository->getFailedCount());
    }

    /**
     * @return void
     *
     * @throws AbortTaskExecutionException
     */
    public function testCleaningFailedTasks(): void
    {
        // Arrange
        $item1 = new QueueItem();
        $item1->setStatus(QueueItem::FAILED);
        $item1->setQueueTimestamp((new DateTime())->sub(new DateInterval('P1D'))->getTimestamp());
        $item2 = new QueueItem();
        $item2->setStatus(QueueItem::FAILED);
        $item2->setQueueTimestamp((new DateTime())->sub(new DateInterval('P13D'))->getTimestamp());
        $item3 = new QueueItem();
        $item3->setStatus(QueueItem::FAILED);
        $item3->setQueueTimestamp((new DateTime())->sub(new DateInterval('P17D'))->getTimestamp());
        $item4 = new QueueItem();
        $item4->setStatus(QueueItem::FAILED);
        $item4->setQueueTimestamp((new DateTime())->sub(new DateInterval('P22D'))->getTimestamp());
        $this->repository->save($item1);
        $this->repository->save($item2);
        $this->repository->save($item3);
        $this->repository->save($item4);

        // Act
        $cleanupTask = new TaskCleanupTask();
        $cleanupTask->execute();

        // Assert
        self::assertEquals(0, $this->taskCleanupRepository->getCompletedCount());
        // item1 and item2 should not be deleted
        self::assertEquals(2, $this->taskCleanupRepository->getFailedCount());
    }
}
