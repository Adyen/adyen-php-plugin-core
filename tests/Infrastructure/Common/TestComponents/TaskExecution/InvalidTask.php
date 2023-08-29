<?php

namespace Adyen\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution;

use Adyen\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException;
use Adyen\Core\Infrastructure\TaskExecution\Task;

/**
 * Class InvalidTask.
 *
 * @package Adyen\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution
 */
class InvalidTask extends Task
{
    public function execute()
    {
    }

    /**
     * @inheritdoc
     * @throws QueueItemDeserializationException
     */
    public function unserialize($serialized)
    {
        throw new QueueItemDeserializationException("Failed to deserialize task.");
    }

    /**
     * @inheritDoc
     * @throws QueueItemDeserializationException
     */
    public static function fromArray(array $array)
    {
        throw new QueueItemDeserializationException("Failed to deserialize task.");
    }
}
