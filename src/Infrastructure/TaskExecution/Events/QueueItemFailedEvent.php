<?php

namespace Adyen\Core\Infrastructure\TaskExecution\Events;

use Adyen\Core\Infrastructure\TaskExecution\QueueItem;

/**
 * Class QueueItemFailedEvent
 *
 * @package Adyen\Core\Infrastructure\TaskExecution\Events
 */
class QueueItemFailedEvent extends BaseQueueItemEvent
{
    /**
     * @var string
     */
    protected $failureDescription;

    /**
     * QueueItemFailedEvent constructor.
     *
     * @param QueueItem $queueItem
     * @param string $failureDescription
     */
    public function __construct(QueueItem $queueItem, $failureDescription)
    {
        parent::__construct($queueItem);
        $this->failureDescription = $failureDescription;
    }

    /**
     * @return string
     */
    public function getFailureDescription()
    {
        return $this->failureDescription;
    }
}
