<?php

namespace Adyen\Core\BusinessLogic\DataAccess\Payment\Contracts;

use Adyen\Core\BusinessLogic\DataAccess\Interfaces\ConditionallyDeletes;
use Adyen\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;

/**
 * Class PaymentsRepository
 *
 * @package Adyen\Core\BusinessLogic\DataAccess\Payment\Contracts
 */
interface PaymentsRepository extends ConditionallyDeletes, RepositoryInterface
{

}
