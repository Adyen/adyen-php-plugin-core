<?php

namespace Adyen\Core\Tests\BusinessLogic\AdminAPI\ShopNotifications\MockComponents;

use Adyen\Core\BusinessLogic\DataAccess\Notifications\Contracts\ShopNotificationRepository;
use Adyen\Core\Tests\BusinessLogic\Common\MockComponents\MemoryRepositoryWithConditionalDelete;
use Adyen\Core\Tests\BusinessLogic\Common\MockComponents\MockConditionalDelete;

class MockShopNotificationsRepository extends MemoryRepositoryWithConditionalDelete implements ShopNotificationRepository
{

    use MockConditionalDelete;

    const THIS_CLASS_NAME = __CLASS__;
}
