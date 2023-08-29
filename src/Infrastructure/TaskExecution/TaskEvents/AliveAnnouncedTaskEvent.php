<?php

namespace Adyen\Core\Infrastructure\TaskExecution\TaskEvents;

use Adyen\Core\Infrastructure\Utility\Events\Event;

/**
 * Class AliveAnnouncedTaskEvent.
 *
 * @package Adyen\Core\Infrastructure\TaskExecution\TaskEvents
 */
class AliveAnnouncedTaskEvent extends Event
{
    /**
     * Fully qualified name of this class.
     */
    const CLASS_NAME = __CLASS__;
}
