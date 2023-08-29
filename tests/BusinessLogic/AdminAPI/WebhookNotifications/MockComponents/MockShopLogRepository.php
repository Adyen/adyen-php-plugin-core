<?php

namespace Adyen\Core\Tests\BusinessLogic\AdminAPI\WebhookNotifications\MockComponents;

use Adyen\Core\BusinessLogic\DataAccess\TransactionLog\Contracts\ShopLogsRepository;
use Adyen\Core\Tests\BusinessLogic\Common\MockComponents\MemoryRepositoryWithConditionalDelete;
use Adyen\Core\Tests\BusinessLogic\Common\MockComponents\MockConditionalDelete;

class MockShopLogRepository extends MemoryRepositoryWithConditionalDelete implements ShopLogsRepository
{

    use MockConditionalDelete;

    const THIS_CLASS_NAME = __CLASS__;
}
