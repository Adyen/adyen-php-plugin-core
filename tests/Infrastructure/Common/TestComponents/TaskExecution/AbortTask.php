<?php

namespace Adyen\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution;

use Adyen\Core\Infrastructure\TaskExecution\Exceptions\AbortTaskExecutionException;
use Adyen\Core\Infrastructure\TaskExecution\Task;

/**
 * Class AbortTask.
 *
 * @package Adyen\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution
 */
class AbortTask extends Task
{
    public function execute()
    {
        throw new AbortTaskExecutionException('Abort mission!');
    }

    /**
     * @inheritDoc
     */
    public static function fromArray(array $array)
    {
        return new static();
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return array();
    }
}
