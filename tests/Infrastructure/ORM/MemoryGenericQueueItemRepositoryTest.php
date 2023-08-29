<?php

namespace Adyen\Core\Tests\Infrastructure\ORM;

use Adyen\Core\Tests\Infrastructure\Common\TestComponents\ORM\MemoryQueueItemRepository;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\ORM\MemoryStorage;

/**
 * Class MemoryGenericQueueItemRepositoryTest.
 *
 * @package Adyen\Core\Tests\Infrastructure\ORM
 */
class MemoryGenericQueueItemRepositoryTest extends AbstractGenericQueueItemRepositoryTest
{
    /**
     * @return string
     */
    public function getQueueItemEntityRepositoryClass()
    {
        return MemoryQueueItemRepository::getClassName();
    }

    /**
     * Cleans up all storage Services used by repositories
     */
    public function cleanUpStorage()
    {
        MemoryStorage::reset();
    }
}
