<?php

namespace Adyen\Core\BusinessLogic\TransactionLog\Listeners;

use Adyen\Core\Infrastructure\TaskExecution\Events\QueueItemFailedEvent;

/**
 * Class FailedListener
 *
 * @package Adyen\Core\BusinessLogic\TransactionLog\Listeners
 */
class FailedListener extends UpdateListener
{
    /**
     * @var QueueItemFailedEvent
     */
    protected $event;

    /**
     * @inheritdoc
     */
    protected function save(): void
    {
        $this->transactionLog->setFailureDescription($this->event->getFailureDescription());

        parent::save();
    }
}
