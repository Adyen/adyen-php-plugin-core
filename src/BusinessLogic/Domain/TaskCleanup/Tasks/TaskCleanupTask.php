<?php

namespace Adyen\Core\BusinessLogic\Domain\TaskCleanup\Tasks;

use Adyen\Core\BusinessLogic\Domain\TaskCleanup\Interfaces\TaskCleanupRepository;
use Adyen\Core\Infrastructure\ServiceRegister;
use Adyen\Core\Infrastructure\TaskExecution\Interfaces\Priority;
use Adyen\Core\Infrastructure\TaskExecution\Task;
use DateInterval;
use DateTime;
use Exception;

/**
 * Class TaskCleanupTask
 * In charge for deleting from the database tasks in specific statuses older than specific age (in seconds).
 *
 * @package Adyen\Core\BusinessLogic\Domain\TaskCleanup\Tasks
 */
class TaskCleanupTask extends Task
{
    /**
     * @return int
     */
    public function getPriority(): int
    {
        return Priority::LOW;
    }

    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    public function execute(): void
    {
        $this->deleteCompletedTasks();
        $this->reportProgress(50);
        $this->deleteFailedTasks();

        $this->reportProgress(100);
    }

    /**
     * @return void
     */
    protected function deleteCompletedTasks(): void
    {
        $repository = $this->getTaskCleanupRepository();
        $completedCount = $repository->getCompletedCount();
        $deletedCount = 0;
        $limit = 5000;

        while ($completedCount > $deletedCount) {
            $repository->deleteCompletedTasks($limit);
            $this->reportAlive();

            $deletedCount += $limit;
        }
    }

    /**
     * @return void
     */
    protected function deleteFailedTasks(): void
    {
        $repository = $this->getTaskCleanupRepository();
        $failedCount = $repository->getFailedCount();
        $date = (new DateTime())->sub(new DateInterval('P14D'));
        $limit = 5000;
        $deletedCount = 0;

        while ($failedCount > $deletedCount) {
            $repository->deleteFailedTasks($date, $limit);
            $this->reportAlive();

            $deletedCount += $limit;
        }
    }

    /**
     * @return TaskCleanupRepository
     */
    private function getTaskCleanupRepository(): TaskCleanupRepository
    {
        return ServiceRegister::getService(TaskCleanupRepository::class);
    }
}
