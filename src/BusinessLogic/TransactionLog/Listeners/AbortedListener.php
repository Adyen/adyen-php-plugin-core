<?php

namespace Adyen\Core\BusinessLogic\TransactionLog\Listeners;

use Adyen\Core\Infrastructure\TaskExecution\Events\QueueItemAbortedEvent;

/**
 * Class AbortedListener
 *
 * @package Adyen\Core\BusinessLogic\TransactionLog\Listeners
 */
class AbortedListener extends UpdateListener
{
    /**
     * @var QueueItemAbortedEvent
     */
    protected $event;

    /**
     * @inheritdoc
     */
    protected function save(): void
    {
        $this->transactionLog->setFailureDescription($this->event->getAbortDescription());

        parent::save();
    }
}
