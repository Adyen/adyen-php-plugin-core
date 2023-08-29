<?php

namespace Adyen\Core\BusinessLogic\Domain\NotificationsRemover\Listeners;

use Adyen\Core\BusinessLogic\Domain\NotificationsRemover\Tasks\NotificationsRemoverTask;
use Adyen\Core\Infrastructure\ServiceRegister;
use Adyen\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;
use Adyen\Core\Infrastructure\TaskExecution\QueueService;
use DateInterval;
use DateTime;

/**
 * Class NotificationsRemoverListener
 *
 * @package Adyen\Core\BusinessLogic\Domain\NotificationsRemover\Listeners
 */
class NotificationsRemoverListener
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
        $task = $this->getQueueService()->findLatestByType(NotificationsRemoverTask::getClassName());

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
        $this->getQueueService()->enqueue('notifications-cleanup', new NotificationsRemoverTask());
    }

    /**
     * @return QueueService
     */
    protected function getQueueService(): QueueService
    {
        return ServiceRegister::getService(QueueService::class);
    }
}
