<?php

namespace Adyen\Core\BusinessLogic\AdyenAPI\Exceptions;

use Adyen\Core\Infrastructure\Exceptions\BaseException;
use Throwable;

/**
 * Class ConnectionSettingsNotFoundException
 *
 * @package Adyen\Core\BusinessLogic\AdyenAPI\Exceptions
 */
class ConnectionSettingsNotFoundException extends BaseException
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, 401, $previous);
    }
}
