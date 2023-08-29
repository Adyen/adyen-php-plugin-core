<?php

namespace Adyen\Core\BusinessLogic\Domain\Connection\Repositories;

use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionSettings;

/**
 * Class ConnectionSettingsRepository
 *
 * @package Adyen\Core\BusinessLogic\Domain\Connection\Repositories
 */
interface ConnectionSettingsRepository
{
    /**
     * Retrieves connection settings for current shop context.
     *
     * @return ConnectionSettings | null
     */
    public function getConnectionSettings(): ?ConnectionSettings;

    /**
     * Retrieves first connection settings.
     *
     * @return ConnectionSettings | null
     */
    public function getOldestConnectionSettings(): ?ConnectionSettings;

    /**
     * Gets active connection setting data based on a selected mode.
     *
     * @return ConnectionData|null
     */
    public function getActiveConnectionData(): ?ConnectionData;

    /**
     * Sets connection settings.
     *
     * @param ConnectionSettings $connectionSettings
     *
     * @return void
     */
    public function setConnectionSettings(ConnectionSettings $connectionSettings): void;

    /**
     * Deletes saved connection settings.
     *
     * @return void
     */
    public function deleteConnectionSettings(): void;

    /**
     * Retrieves all connection settings for all connected stores.
     *
     * @return ConnectionSettings[]
     */
    public function getAllConnectionSettings(): array;
}
