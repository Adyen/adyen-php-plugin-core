<?php

namespace Adyen\Core\Infrastructure\ORM\Interfaces;

use Adyen\Core\Infrastructure\ORM\QueryFilter\QueryFilter;

/**
 * Interface ConditionallyDeletes
 * @package Adyen\Core\BusinessLogic\ORM\Interfaces
 */
interface ConditionallyDeletes
{
    public function deleteWhere(QueryFilter $queryFilter = null);
}
