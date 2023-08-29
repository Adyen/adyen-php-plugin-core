<?php

namespace Adyen\Core\Infrastructure\AutoTest;

use Adyen\Core\Infrastructure\Logger\Interfaces\ShopLoggerAdapter;
use Adyen\Core\Infrastructure\Logger\LogData;
use Adyen\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use Adyen\Core\Infrastructure\ORM\RepositoryRegistry;
use Adyen\Core\Infrastructure\Singleton;

/**
 * Class AutoTestLogger.
 *
 * @package Adyen\Core\Infrastructure\AutoConfiguration
 */
class AutoTestLogger extends Singleton implements ShopLoggerAdapter
{
    /**
     * Singleton instance of this class.
     *
     * @var static
     */
    protected static $instance;

    /**
     * Logs a message in system.
     *
     * @param LogData $data Data to log.
     *
     * @throws RepositoryNotRegisteredException
     */
    public function logMessage(LogData $data)
    {
        $repo = RepositoryRegistry::getRepository(LogData::CLASS_NAME);
        $repo->save($data);
    }

    /**
     * Gets all log entities.
     *
     * @return LogData[] An array of the LogData entities, if any.
     *
     * @throws RepositoryNotRegisteredException
     */
    public function getLogs()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return RepositoryRegistry::getRepository(LogData::CLASS_NAME)->select();
    }

    /**
     * Transforms logs to the plain array.
     *
     * @return array An array of logs.
     *
     * @throws RepositoryNotRegisteredException
     */
    public function getLogsArray()
    {
        $result = array();
        foreach ($this->getLogs() as $log) {
            $result[] = $log->toArray();
        }

        return $result;
    }
}
