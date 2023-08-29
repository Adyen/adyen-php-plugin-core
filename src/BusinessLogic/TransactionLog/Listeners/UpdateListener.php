<?php

namespace Adyen\Core\BusinessLogic\TransactionLog\Listeners;

use Adyen\Core\BusinessLogic\DataAccess\TransactionLog\Entities\TransactionLog;
use Adyen\Core\BusinessLogic\TransactionLog\Contracts\TransactionLogAware;
use Adyen\Core\Infrastructure\TaskExecution\Events\BaseQueueItemEvent;
use Adyen\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException;
use Adyen\Core\Infrastructure\TaskExecution\QueueItem;

/**
 * Class UpdateListener
 *
 * @package Adyen\Core\BusinessLogic\TransactionLog\Listeners
 */
class UpdateListener extends Listener
{
    /**
     * @var TransactionLog
     */
    protected $transactionLog;

    /**
     * @var BaseQueueItemEvent
     */
    protected $event;

    /**
     * @var QueueItem
     */
    protected $queueItem;

    /**
     * @inheritDoc
     *
     * @throws QueueItemDeserializationException
     */
    protected function doHandle(BaseQueueItemEvent $event): void
    {
        $this->init($event);

        $this->transactionLog->setQueueStatus($this->queueItem->getStatus());

        $this->save();
    }

    /**
     * @throws QueueItemDeserializationException
     */
    protected function init(BaseQueueItemEvent $event): void
    {
        $this->event = $event;
        $this->queueItem = $this->extractQueueItem();

        /** @var TransactionLogAware $task */
        $task = $this->queueItem->getTask();
        $this->transactionLog = $task->getTransactionLog();
    }

    /**
     * @inheritdoc
     */
    protected function canHandle(BaseQueueItemEvent $event): bool
    {
        if (!parent::canHandle($event)) {
            return false;
        }

        $queueItem = $event->getQueueItem();

        /** @var TransactionLogAware $task */
        $task = $queueItem->getTask();

        return !(!$task || !$task->getTransactionLog());
    }

    /**
     * @return void
     */
    protected function save(): void
    {
        $this->getService()->update($this->transactionLog);
    }

    /**
     * @return QueueItem
     */
    protected function extractQueueItem(): QueueItem
    {
        return $this->event->getQueueItem();
    }
}
