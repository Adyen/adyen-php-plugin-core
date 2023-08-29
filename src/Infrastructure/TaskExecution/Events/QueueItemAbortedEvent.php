<?php

namespace Adyen\Core\Infrastructure\TaskExecution\Events;

use Adyen\Core\Infrastructure\TaskExecution\QueueItem;

/**
 * Class QueueItemAbortedEvent
 *
 * @package Adyen\Core\Infrastructure\TaskExecution\Events
 */
class QueueItemAbortedEvent extends BaseQueueItemEvent
{
    protected $abortDescription;

    /**
     * QueueItemAbortedEvent constructor.
     *
     * @param QueueItem $queueItem
     * @param $abortDescription
     */
    public function __construct(QueueItem $queueItem, $abortDescription)
    {
        parent::__construct($queueItem);
        $this->abortDescription = $abortDescription;
    }

    /**
     * @return mixed
     */
    public function getAbortDescription()
    {
        return $this->abortDescription;
    }
}
