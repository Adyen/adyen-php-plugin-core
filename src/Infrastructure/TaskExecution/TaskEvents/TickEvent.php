<?php

namespace Adyen\Core\Infrastructure\TaskExecution\TaskEvents;

use Adyen\Core\Infrastructure\Utility\Events\Event;

/**
 * Class TickEvent.
 *
 * @package Adyen\Core\Infrastructure\Scheduler
 */
class TickEvent extends Event
{
    /**
     * Fully qualified name of this class.
     */
    const CLASS_NAME = __CLASS__;
}
