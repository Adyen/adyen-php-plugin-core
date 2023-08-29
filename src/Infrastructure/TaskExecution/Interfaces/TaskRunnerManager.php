<?php

namespace Adyen\Core\Infrastructure\TaskExecution\Interfaces;

/**
 * Interface TaskRunnerManager
 *
 * @package Adyen\Core\Infrastructure\TaskExecution\Interfaces
 */
interface TaskRunnerManager
{
    const CLASS_NAME = __CLASS__;

    /**
     * Halts task runner.
     */
    public function halt();

    /**
     * Resumes task execution.
     */
    public function resume();
}
