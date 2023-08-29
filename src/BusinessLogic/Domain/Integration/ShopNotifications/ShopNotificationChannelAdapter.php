<?php

namespace Adyen\Core\BusinessLogic\Domain\Integration\ShopNotifications;

use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Event;

/**
 * Interface ShopNotificationChannelAdapter
 *
 * @package Adyen\Core\BusinessLogic\Domain\Integration\ShopNotifications
 */
interface ShopNotificationChannelAdapter
{
    /**
     * Pushes event to the shop notification channels
     *
     * @param Event $notification
     */
    public function push(Event $notification): void;
}
