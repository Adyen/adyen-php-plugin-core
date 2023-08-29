<?php

namespace Adyen\Core\Tests\Infrastructure\Common\TestComponents\Utility\Events;

use Adyen\Core\Infrastructure\Utility\Events\Event;
use Adyen\Core\Infrastructure\Utility\Events\EventEmitter;

class TestEventEmitter extends EventEmitter
{
    /**
     * Singleton instance of this class.
     *
     * @var TestEventEmitter
     */
    protected static $instance;

    public static function getInstance()
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    public function fire(Event $event)
    {
        parent::fire($event);
    }
}
