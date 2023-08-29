<?php

namespace Adyen\Core\BusinessLogic\DataAccess\Interfaces;

use Adyen\Core\Infrastructure\ORM\QueryFilter\QueryFilter;

/**
 * Class ConditionallyDeletes
 *
 * @package Adyen\Core\BusinessLogic\DataAccess\Interfaces
 */
interface ConditionallyDeletes
{
    /**
     * @param QueryFilter|null $queryFilter
     *
     * @return void
     */
    public function deleteWhere(QueryFilter $queryFilter = null): void;
}
