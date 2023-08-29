<?php

namespace Adyen\Core\Tests\BusinessLogic\Domain\TaskCleanup\Mocks;

use Adyen\Core\Tests\BusinessLogic\Common\MockComponents\MockConditionalDelete;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\ORM\MemoryQueueItemRepository;
use Adyen\Core\BusinessLogic\ORM\Interfaces\QueueItemRepository as QueueItemRepositoryInterface;

/**
 * Class QueueItemRepository
 *
 * @package Adyen\Core\Tests\BusinessLogic\Domain\TaskCleanup\Mocks
 */
class QueueItemRepository extends MemoryQueueItemRepository implements QueueItemRepositoryInterface
{
    use MockConditionalDelete;
}
