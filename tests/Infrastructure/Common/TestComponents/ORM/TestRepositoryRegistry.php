<?php

namespace Adyen\Core\Tests\Infrastructure\Common\TestComponents\ORM;

use Adyen\Core\Infrastructure\ORM\RepositoryRegistry;

class TestRepositoryRegistry extends RepositoryRegistry
{
    public static function cleanUp()
    {
        static::$repositories = array();
        static::$instantiated = array();
    }
}
