<?php

namespace Adyen\Core\BusinessLogic\Domain\Connection\Models;

use Adyen\Core\BusinessLogic\Domain\Connection\Enums\Mode;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\EmptyConnectionDataException;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\EmptyStoreException;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidModeException;
use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class ConnectionSettings
 *
 * @package Adyen\Core\BusinessLogic\Domain\Connection\Models
 */
class ConnectionSettings
{
    /**
     * @var string
     */
    protected $storeId;
    /**
     * @var string
     */
    protected $mode;
    /**
     * @var ConnectionData
     */
    protected $testData;
    /**
     * @var ConnectionData
     */
    protected $liveData;

    /**
     * @param string $storeId
     * @param string $mode
     * @param ConnectionData|null $testData
     * @param ConnectionData|null $liveData
     *
     * @throws InvalidModeException
     * @throws EmptyStoreException
     * @throws EmptyConnectionDataException
     */
    public function __construct(string $storeId, string $mode, ?ConnectionData $testData, ?ConnectionData $liveData)
    {
        if (empty($storeId)) {
            throw new EmptyStoreException(
                new TranslatableLabel('Empty store id.', 'connection.emptyStoreId')
            );
        }

        if (!in_array($mode, [Mode::MODE_LIVE, Mode::MODE_TEST], true)) {
            throw new InvalidModeException(new TranslatableLabel('Invalid mode.', 'connection.invalidMode'));
        }

        if (($mode === Mode::MODE_TEST && $testData === null) || ($mode === Mode::MODE_LIVE && $liveData === null)) {
            throw new EmptyConnectionDataException(
                new TranslatableLabel('Empty connection data.', 'connection.emptyData')
            );
        }

        $this->storeId = $storeId;
        $this->mode = $mode;
        $this->testData = $testData;
        $this->liveData = $liveData;
    }

    /**
     * @return string
     */
    public function getStoreId(): string
    {
        return $this->storeId;
    }

    /**
     * @return string
     */
    public function getMode(): string
    {
        return $this->mode;
    }

    /**
     * Gets active connection setting data based on a selected mode
     *
     * @return ConnectionData
     */
    public function getActiveConnectionData(): ConnectionData
    {
        return $this->getMode() === Mode::MODE_LIVE ? $this->getLiveData() : $this->getTestData();
    }

    /**
     * @return ConnectionData|null
     */
    public function getTestData(): ?ConnectionData
    {
        return $this->testData;
    }

    /**
     * @return ConnectionData|null
     */
    public function getLiveData(): ?ConnectionData
    {
        return $this->liveData;
    }

    /**
     * @param ConnectionData|null $testData
     */
    public function setTestData(?ConnectionData $testData): void
    {
        $this->testData = $testData;
    }

    /**
     * @param ConnectionData|null $liveData
     */
    public function setLiveData(?ConnectionData $liveData): void
    {
        $this->liveData = $liveData;
    }
}
