<?php

namespace Adyen\Core\Tests\BusinessLogic\Common\MockComponents;

use Adyen\Core\Infrastructure\ORM\Interfaces\ConditionallyDeletes;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\ORM\MemoryQueueItemRepository;

class MemoryQueueItemRepositoryWithConditionalDelete extends MemoryQueueItemRepository implements ConditionallyDeletes
{
    use MockConditionalDelete;

    const THIS_CLASS_NAME = __CLASS__;
}
