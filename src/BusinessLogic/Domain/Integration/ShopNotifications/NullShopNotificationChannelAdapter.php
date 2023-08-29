<?php

namespace Adyen\Core\BusinessLogic\Domain\Integration\ShopNotifications;

use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Event;

/**
 * Class NullShopNotificationChannelAdapter
 *
 * @package Adyen\Core\BusinessLogic\Domain\Integration\ShopNotifications
 */
class NullShopNotificationChannelAdapter implements ShopNotificationChannelAdapter
{
    public function push(Event $notification): void
    {
    }
}
