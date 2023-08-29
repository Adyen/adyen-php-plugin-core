<?php

namespace Adyen\Core\BusinessLogic\TransactionLog\Listeners;

use Adyen\Core\Infrastructure\TaskExecution\Events\BaseQueueItemEvent;
use Adyen\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException;

/**
 * Class CreateListener
 *
 * @package Adyen\Core\BusinessLogic\TransactionLog\Listeners
 */
class CreateListener extends Listener
{
    /**
     * @inheritDoc
     *
     * @throws QueueItemDeserializationException
     */
    protected function doHandle(BaseQueueItemEvent $event): void
    {
        $queueItem = $event->getQueueItem();

        if ($queueItem->getParentId() !== null) {
            return;
        }

        if ($this->getService()->hasQueueItem($queueItem)) {
            $this->getService()->load($queueItem);

            return;
        }

        $this->getService()->create($queueItem);
    }
}
