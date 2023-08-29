<?php

namespace Adyen\Core\BusinessLogic\DataAccess\Notifications\Contracts;

use Adyen\Core\BusinessLogic\DataAccess\Interfaces\ConditionallyDeletes;
use Adyen\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;

/**
 * Class ShopNotificationRepository
 *
 * @package Adyen\Core\BusinessLogic\DataAccess\Notifications\Contracts
 */
interface ShopNotificationRepository extends ConditionallyDeletes, RepositoryInterface
{

}
