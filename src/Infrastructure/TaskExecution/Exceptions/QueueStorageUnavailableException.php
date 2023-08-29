<?php

namespace Adyen\Core\Infrastructure\TaskExecution\Exceptions;

use Adyen\Core\Infrastructure\Exceptions\BaseException;
use Exception;

/**
 * Class QueueStorageUnavailableException.
 *
 * @package Adyen\Core\Infrastructure\TaskExecution\Exceptions
 */
class QueueStorageUnavailableException extends BaseException
{
    /**
     * QueueStorageUnavailableException constructor.
     *
     * @param string $message Exceptions message.
     * @param Exception $previous Exceptions instance that was thrown.
     */
    public function __construct($message = '', $previous = null)
    {
        parent::__construct(trim($message . ' Queue storage failed to save item.'), 0, $previous);
    }
}
