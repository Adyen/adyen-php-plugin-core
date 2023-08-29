<?php

namespace Adyen\Core\BusinessLogic\TransactionLog\Tasks;

use Adyen\Core\BusinessLogic\TransactionLog\Contracts\TransactionLogAware as TransactionLogAwareInterface;
use Adyen\Core\BusinessLogic\TransactionLog\Traits\TransactionLogAware;
use Adyen\Core\Infrastructure\TaskExecution\Task;

/**
 * Class TransactionalTask
 *
 * @package Adyen\Core\BusinessLogic\TransactionLog\Tasks
 */
abstract class TransactionalTask extends Task implements TransactionLogAwareInterface
{
    use TransactionLogAware;
}
