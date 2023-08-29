<?php

namespace Adyen\Core\BusinessLogic\DataAccess\TransactionLog\Contracts;

use Adyen\Core\BusinessLogic\DataAccess\Interfaces\ConditionallyDeletes;
use Adyen\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;

/**
 * Class ShopLogsRepository
 *
 * @package Adyen\Core\BusinessLogic\DataAccess\TransactionLog\Contracts
 */
interface ShopLogsRepository extends ConditionallyDeletes, RepositoryInterface
{

}
