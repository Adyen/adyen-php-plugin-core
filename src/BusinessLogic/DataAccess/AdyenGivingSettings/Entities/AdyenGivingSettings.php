<?php

namespace Adyen\Core\BusinessLogic\DataAccess\AdyenGivingSettings\Entities;

use Adyen\Core\Infrastructure\ORM\Configuration\EntityConfiguration;
use Adyen\Core\Infrastructure\ORM\Configuration\IndexMap;
use Adyen\Core\Infrastructure\ORM\Entity;
use Adyen\Core\BusinessLogic\Domain\AdyenGivingSettings\Models\AdyenGivingSettings as DomainAdyenGivingSettings;

/**
 * Class AdyenGivingSettings
 *
 * @package Adyen\Core\BusinessLogic\DataAccess\AdyenGivingSettings\Entities
 */
class AdyenGivingSettings extends Entity
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
     * @var DomainAdyenGivingSettings
     */
    protected $adyenGivingSettings;

    /**
     * @inheritDoc
     */
    public function inflate(array $data): void
    {
        parent::inflate($data);

        $adyenGivingSettings = $data['adyenGivingSettings'] ?? [];

        $this->adyenGivingSettings = new DomainAdyenGivingSettings(
            self::getDataValue($adyenGivingSettings, 'enableAdyenGiving'),
            self::getDataValue($adyenGivingSettings, 'charityName'),
            self::getDataValue($adyenGivingSettings, 'charityDescription'),
            self::getDataValue($adyenGivingSettings, 'charityMerchantAccount'),
            self::getDataValue($adyenGivingSettings, 'donationAmount'),
            self::getDataValue($adyenGivingSettings, 'charityWebsite'),
            self::getDataValue($adyenGivingSettings, 'logo'),
            self::getDataValue($adyenGivingSettings, 'backgroundImage')
        );
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $data = parent::toArray();

        $data['storeId'] = $this->storeId;
        $data['adyenGivingSettings'] = $this->adyenGivingSettingsToArray($this->adyenGivingSettings);

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function getConfig(): EntityConfiguration
    {
        $indexMap = new IndexMap();

        $indexMap->addStringIndex('storeId');

        return new EntityConfiguration($indexMap, 'AdyenGivingSettings');
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
     * @return DomainAdyenGivingSettings
     */
    public function getAdyenGivingSettings(): DomainAdyenGivingSettings
    {
        return $this->adyenGivingSettings;
    }

    /**
     * @param DomainAdyenGivingSettings $adyenGivingSettings
     */
    public function setAdyenGivingSettings(DomainAdyenGivingSettings $adyenGivingSettings): void
    {
        $this->adyenGivingSettings = $adyenGivingSettings;
    }

    /**
     * Transforms Domain AdyenGivingSettings to its array representation.
     *
     * @param DomainAdyenGivingSettings $adyenGivingSettings
     *
     * @return array
     */
    private function adyenGivingSettingsToArray(DomainAdyenGivingSettings $adyenGivingSettings): array
    {
        return [
            'enableAdyenGiving' => $adyenGivingSettings->isEnableAdyenGiving(),
            'charityName' => $adyenGivingSettings->getCharityName(),
            'charityDescription' => $adyenGivingSettings->getCharityDescription(),
            'charityMerchantAccount' => $adyenGivingSettings->getCharityMerchantAccount(),
            'donationAmount' => $adyenGivingSettings->getDonationAmounts(),
            'charityWebsite' => $adyenGivingSettings->getCharityWebsite(),
            'logo' => $adyenGivingSettings->getLogo(),
            'backgroundImage' => $adyenGivingSettings->getBackgroundImage()
        ];
    }
}
