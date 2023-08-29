<?php

namespace Adyen\Core\Infrastructure\Logger\Interfaces;

use Adyen\Core\Infrastructure\Logger\LogData;

/**
 * Interface LoggerAdapter.
 *
 * @package Adyen\Core\Infrastructure\Logger\Interfaces
 */
interface LoggerAdapter
{
    /**
     * Log message in system
     *
     * @param LogData $data
     */
    public function logMessage(LogData $data);
}
