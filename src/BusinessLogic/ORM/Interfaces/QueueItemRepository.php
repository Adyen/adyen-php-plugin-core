<?php

namespace Adyen\Core\BusinessLogic\ORM\Interfaces;

use Adyen\Core\BusinessLogic\DataAccess\Interfaces\ConditionallyDeletes;
use Adyen\Core\Infrastructure\ORM\Interfaces\QueueItemRepository as InfrastructureQueueItemRepository;

/**
 * Interface QueueItemRepository
 *
 * @package Adyen\Core\BusinessLogic\ORM\Interfaces
 */
interface QueueItemRepository extends ConditionallyDeletes, InfrastructureQueueItemRepository
{

}
