<?php

namespace Adyen\Core\Infrastructure\TaskExecution\Interfaces;

use Adyen\Core\Infrastructure\Serializer\Interfaces\Serializable;

/**
 * Interface Runnable.
 *
 * @package Adyen\Core\Infrastructure\TaskExecution\Interfaces
 */
interface Runnable extends Serializable
{
    /**
     * Starts runnable run logic
     */
    public function run();
}
