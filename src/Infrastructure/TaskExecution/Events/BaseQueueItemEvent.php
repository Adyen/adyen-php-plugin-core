<?php

namespace Adyen\Core\Infrastructure\TaskExecution\Events;

use Adyen\Core\Infrastructure\TaskExecution\QueueItem;
use Adyen\Core\Infrastructure\Utility\Events\Event;

/**
 * Class BaseQueueItemEvent
 *
 * @package Adyen\Core\Infrastructure\TaskExecution\Events
 */
abstract class BaseQueueItemEvent extends Event
{
    /**
     * @var QueueItem
     */
    protected $queueItem;

    /**
     * BaseQueueItemEvent constructor.
     *
     * @param QueueItem $queueItem
     */
    public function __construct(QueueItem $queueItem)
    {
        $this->queueItem = $queueItem;
    }

    /**
     * @return QueueItem
     */
    public function getQueueItem()
    {
        return $this->queueItem;
    }
}
