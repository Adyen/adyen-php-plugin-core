<?php

namespace Adyen\Core\Tests\BusinessLogic\DataAccess\Connection\Entities;

use Adyen\Core\BusinessLogic\DataAccess\Connection\Entities\ConnectionSettings;
use Adyen\Core\Tests\Infrastructure\ORM\Entity\GenericEntityTest;

class ConnectionSettingsTest extends GenericEntityTest
{

    /**
     * @inheritDoc
     */
    public function getEntityClass(): string
    {
        return ConnectionSettings::getClassName();
    }
}
