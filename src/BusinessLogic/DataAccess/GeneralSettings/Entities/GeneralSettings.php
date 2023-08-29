<?php

namespace Adyen\Core\BusinessLogic\DataAccess\GeneralSettings\Entities;

use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Exceptions\InvalidCaptureDelayException;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Exceptions\InvalidCaptureTypeException;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Exceptions\InvalidRetentionPeriodException;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Models\CaptureType;
use Adyen\Core\Infrastructure\ORM\Configuration\EntityConfiguration;
use Adyen\Core\Infrastructure\ORM\Configuration\IndexMap;
use Adyen\Core\Infrastructure\ORM\Entity;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Models\GeneralSettings as DomainGeneralSettings;

/**
 * Class GeneralSettings
 *
 * @package Adyen\Core\BusinessLogic\DataAccess\GeneralSettings\Entities
 */
class GeneralSettings extends Entity
{
    /**
     * Fully qualified name of this class.
     */
    public const CLASS_NAME = __CLASS__;

    /**
     * @var string
     */
    protected $storeId;

    /**
     * @var DomainGeneralSettings
     */
    protected $generalSettings;

    /**
     * @inheritDoc
     *
     * @throws InvalidCaptureDelayException
     * @throws InvalidRetentionPeriodException
     * @throws InvalidCaptureTypeException
     */
    public function inflate(array $data): void
    {
        parent::inflate($data);

        $generalSettings = $data['generalSettings'] ?? [];

        $this->generalSettings = new DomainGeneralSettings(
            self::getDataValue($generalSettings, 'basketItemSync'),
            CaptureType::fromState(self::getDataValue($generalSettings, 'capture')),
            self::getDataValue($generalSettings, 'captureDelay'),
            self::getDataValue($generalSettings, 'shipmentStatus'),
            self::getDataValue($generalSettings, 'retentionPeriod')
        );
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $data = parent::toArray();

        $data['storeId'] = $this->storeId;
        $data['generalSettings'] = $this->generalSettingsToArray();

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function getConfig(): EntityConfiguration
    {
        $indexMap = new IndexMap();

        $indexMap->addStringIndex('storeId');

        return new EntityConfiguration($indexMap, 'GeneralSettings');
    }

    /**
     * @return string
     */
    public function getStoreId(): string
    {
        return $this->storeId;
    }

    /**
     * @param string $storeId
     */
    public function setStoreId(string $storeId): void
    {
        $this->storeId = $storeId;
    }

    /**
     * @return bool
     */
    public function isBasketItemSync(): bool
    {
        return $this->basketItemSync;
    }

    /**
     * @param bool $basketItemSync
     */
    public function setBasketItemSync(bool $basketItemSync): void
    {
        $this->basketItemSync = $basketItemSync;
    }

    /**
     * @return string
     */
    public function getCapture(): string
    {
        return $this->capture;
    }

    /**
     * @param string $capture
     */
    public function setCapture(string $capture): void
    {
        $this->capture = $capture;
    }

    /**
     * @return int
     */
    public function getCaptureDelay(): int
    {
        return $this->captureDelay;
    }

    /**
     * @param int $captureDelay
     */
    public function setCaptureDelay(int $captureDelay): void
    {
        $this->captureDelay = $captureDelay;
    }

    /**
     * @return string
     */
    public function getShipmentStatus(): string
    {
        return $this->shipmentStatus;
    }

    /**
     * @param string $shipmentStatus
     */
    public function setShipmentStatus(string $shipmentStatus): void
    {
        $this->shipmentStatus = $shipmentStatus;
    }

    /**
     * @return int
     */
    public function getRetentionPeriod(): int
    {
        return $this->retentionPeriod;
    }

    /**
     * @param int $retentionPeriod
     */
    public function setRetentionPeriod(int $retentionPeriod): void
    {
        $this->retentionPeriod = $retentionPeriod;
    }

    /**
     * @return DomainGeneralSettings
     */
    public function getGeneralSettings(): DomainGeneralSettings
    {
        return $this->generalSettings;
    }

    /**
     * @param DomainGeneralSettings $generalSettings
     */
    public function setGeneralSettings(DomainGeneralSettings $generalSettings): void
    {
        $this->generalSettings = $generalSettings;
    }

    /**
     * Transforms Domain GeneralSettings to its array representation.
     *
     * @return array
     */
    private function generalSettingsToArray(): array
    {
        return [
            'basketItemSync' => $this->generalSettings->isBasketItemSync(),
            'capture' => $this->generalSettings->getCapture()->getType(),
            'captureDelay' => $this->generalSettings->getCaptureDelay(),
            'shipmentStatus' => $this->generalSettings->getShipmentStatus(),
            'retentionPeriod' => $this->generalSettings->getRetentionPeriod()
        ];
    }
}
