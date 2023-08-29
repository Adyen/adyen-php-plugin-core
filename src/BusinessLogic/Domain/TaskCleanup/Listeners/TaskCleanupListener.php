<?php

namespace Adyen\Core\BusinessLogic\Domain\TaskCleanup\Listeners;

use Adyen\Core\BusinessLogic\Domain\TaskCleanup\Tasks\TaskCleanupTask;
use Adyen\Core\Infrastructure\ServiceRegister;
use Adyen\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;
use Adyen\Core\Infrastructure\TaskExecution\QueueService;
use DateInterval;
use DateTime;

/**
 * Class TaskCleanupListener
 *
 * @package Adyen\Core\BusinessLogic\Domain\TaskCleanup\Listeners
 */
class TaskCleanupListener
{
    /**
     * @return void
     *
     * @throws QueueStorageUnavailableException
     */
    public function handle(): void
    {
        if (!$this->canHandle()) {
            return;
        }

        $this->doHandle();
    }

    /**
     * @return bool
     */
    protected function canHandle(): bool
    {
        $task = $this->getQueueService()->findLatestByType(TaskCleanupTask::getClassName());

        return !$task ||
            $task->getQueueTimestamp() < (new DateTime())->sub(new DateInterval('P1D'))->getTimestamp();
    }

    /**
     * @return void
     *
     * @throws QueueStorageUnavailableException
     */
    protected function doHandle(): void
    {
        $this->getQueueService()->enqueue('task-cleanup', new TaskCleanupTask());
    }

    /**
     * @return QueueService
     */
    protected function getQueueService(): QueueService
    {
        return ServiceRegister::getService(QueueService::class);
    }
}
