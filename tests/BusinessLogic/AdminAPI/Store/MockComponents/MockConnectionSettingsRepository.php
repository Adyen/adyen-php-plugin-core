<?php

namespace Adyen\Core\Tests\BusinessLogic\AdminAPI\Store\MockComponents;

use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionSettings;
use Adyen\Core\BusinessLogic\Domain\Connection\Repositories\ConnectionSettingsRepository;

/**
 * Class MockConnectionSettingsRepository
 *
 * @package Adyen\Core\Tests\BusinessLogic\AdminAPI\Store\MockComponents
 */
class MockConnectionSettingsRepository implements ConnectionSettingsRepository
{
    /**
     * @var ConnectionSettings
     */
    private $connectionSettings;

    public function __construct()
    {
        $this->connectionSettings = new ConnectionSettings(
            '1',
            'test',
            new ConnectionData('1', '1'),
            null
        );
    }

    /**
     * @inheritDoc
     */
    public function getConnectionSettings(): ?ConnectionSettings
    {
        return $this->connectionSettings;
    }

    /**
     * @param ConnectionSettings|null $connectionSettings
     *
     * @return void
     */
    public function setMockConnectionSettings(?ConnectionSettings $connectionSettings): void
    {
        $this->connectionSettings = $connectionSettings;
    }

    /**
     * @inheritDoc
     */
    public function setConnectionSettings(ConnectionSettings $connectionSettings): void
    {
        $this->setMockConnectionSettings($connectionSettings);
    }

    public function getOldestConnectionSettings(): ?ConnectionSettings
    {
        return $this->connectionSettings;
    }

    public function getActiveConnectionData(): ?ConnectionData
    {
        return $this->connectionSettings ? $this->connectionSettings->getActiveConnectionData() : null;
    }

    public function deleteConnectionSettings(): void
    {
    }

    public function getAllConnectionSettings(): array
    {
        return [];
    }
}
