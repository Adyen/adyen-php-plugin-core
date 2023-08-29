<?php

namespace Adyen\Core\Tests\Infrastructure\ORM\Entity;

use Adyen\Core\Infrastructure\TaskExecution\Process;

/**
 * Class ProcessTest.
 *
 * @package Adyen\Core\Tests\Infrastructure\ORM\Entity
 */
class ProcessTest extends GenericEntityTest
{
    /**
     * Returns entity full class name
     *
     * @return string
     */
    public function getEntityClass()
    {
        return Process::getClassName();
    }
}
