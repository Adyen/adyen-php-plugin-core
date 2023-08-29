<?php

namespace Adyen\Core\Infrastructure\TaskExecution\Events;

use Adyen\Core\Infrastructure\Utility\Events\EventBus;

/**
 * Class QueueItemStateTransitionEventBus
 *
 * @package Adyen\Core\Infrastructure\TaskExecution\Events
 */
class QueueItemStateTransitionEventBus extends EventBus
{
    const CLASS_NAME = __CLASS__;
    /**
     * Singleton instance of this class.
     *
     * @var static
     */
    protected static $instance;
}
