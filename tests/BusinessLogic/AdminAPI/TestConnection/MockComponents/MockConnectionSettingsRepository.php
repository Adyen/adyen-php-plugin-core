<?php

namespace Adyen\Core\Tests\BusinessLogic\AdminAPI\TestConnection\MockComponents;

use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionSettings;
use Adyen\Core\BusinessLogic\Domain\Connection\Repositories\ConnectionSettingsRepository;

/**
 * Class MockConnectionSettingsRepository
 *
 * @package Adyen\Core\Tests\BusinessLogic\AdminAPI\TestConnection\MockComponents
 */
class MockConnectionSettingsRepository implements ConnectionSettingsRepository
{
    /**
     * @var ConnectionSettings
     */
    private $connectionSettingsByStoreContext;

    /**
     * @var ConnectionSettings
     */
    private $connectionSettings;

    /**
     * @inheritDoc
     */
    public function getConnectionSettings(): ?ConnectionSettings
    {
        return $this->connectionSettingsByStoreContext;
    }

    /**
     * @inheritDoc
     */
    public function getOldestConnectionSettings(): ?ConnectionSettings
    {
       return $this->connectionSettings;
    }

    /**
     * @inheritDoc
     */
    public function getActiveConnectionData(): ?ConnectionData
    {
        return $this->connectionSettingsByStoreContext->getActiveConnectionData();
    }

    /**
     * @inheritDoc
     */
    public function setConnectionSettings(ConnectionSettings $connectionSettings): void
    {
        $this->connectionSettings = $connectionSettings;
        $this->connectionSettingsByStoreContext = $connectionSettings;
    }

    public function deleteConnectionSettings(): void
    {
        // TODO: Implement deleteConnectionSettings() method.
    }

    public function getAllConnectionSettings(): array
    {
        return [];
    }
}
