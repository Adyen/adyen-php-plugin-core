<?php

namespace Adyen\Core\BusinessLogic\TransactionLog\Listeners;

use Adyen\Core\Infrastructure\TaskExecution\Events\BaseQueueItemEvent;
use Adyen\Core\Infrastructure\TaskExecution\Events\QueueItemStartedEvent;
use Adyen\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException;

/**
 * Class LoadListener
 *
 * @package Adyen\Core\BusinessLogic\TransactionLog\Listeners
 */
class LoadListener extends Listener
{
    /**
     * @var QueueItemStartedEvent
     */
    protected $event;

    /**
     * @inheritDoc
     *
     * @throws QueueItemDeserializationException
     */
    protected function doHandle(BaseQueueItemEvent $event): void
    {
        $this->getService()->load($event->getQueueItem());
    }
}
