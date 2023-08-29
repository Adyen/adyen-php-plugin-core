<?php

namespace Adyen\Core\Tests\Infrastructure\ORM\Entity;

use Adyen\Core\Infrastructure\TaskExecution\QueueItem;

/**
 * Class QueueItemTest.
 *
 * @package Adyen\Core\Tests\Infrastructure\ORM\Entity
 */
class QueueItemTest extends GenericEntityTest
{
    /**
     * Returns entity full class name
     *
     * @return string
     */
    public function getEntityClass()
    {
        return QueueItem::getClassName();
    }
}
