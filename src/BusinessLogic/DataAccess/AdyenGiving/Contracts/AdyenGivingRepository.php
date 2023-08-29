<?php

namespace Adyen\Core\BusinessLogic\DataAccess\AdyenGiving\Contracts;

use Adyen\Core\BusinessLogic\DataAccess\Interfaces\ConditionallyDeletes;
use Adyen\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;

/**
 * Interface AdyenGivingRepository
 *
 * @package Adyen\Core\BusinessLogic\DataAccess\AdyenGiving\Contracts
 */
interface AdyenGivingRepository extends ConditionallyDeletes, RepositoryInterface
{

}
