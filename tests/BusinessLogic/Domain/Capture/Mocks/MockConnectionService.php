<?php

namespace Adyen\Core\Tests\BusinessLogic\Domain\Capture\Mocks;

use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\EmptyConnectionDataException;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\EmptyStoreException;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidModeException;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionSettings;
use Adyen\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;

/**
 * Class MockConnectionService
 *
 * @package Adyen\Core\Tests\BusinessLogic\Domain\Capture\Mocks
 */
class MockConnectionService extends ConnectionService
{
    /**
     * Retrieves connection settings.
     *
     * @return ConnectionSettings|null
     *
     * @throws EmptyConnectionDataException
     * @throws EmptyStoreException
     * @throws InvalidModeException
     */
    public function getConnectionData(): ?ConnectionSettings
    {
        return new ConnectionSettings('1', 'test', new ConnectionData('key', 'merchantAccount'), null);
    }
}
