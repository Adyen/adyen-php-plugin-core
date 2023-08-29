<?php

namespace Adyen\Core\BusinessLogic\DataAccess\AdyenGiving\Entities;

use Adyen\Core\BusinessLogic\Domain\Checkout\AdyenGiving\Models\DonationsData as DonationsDataModel;
use Adyen\Core\Infrastructure\ORM\Configuration\EntityConfiguration;
use Adyen\Core\Infrastructure\ORM\Configuration\IndexMap;
use Adyen\Core\Infrastructure\ORM\Entity;

/**
 * Class DonationsData
 *
 * @package Adyen\Core\BusinessLogic\DataAccess\AdyenGiving\Entities
 */
class DonationsData extends Entity
{
    /**
     * Fully qualified name of this class.
     */
    public const CLASS_NAME = __CLASS__;
    /**
     * @var DonationsDataModel
     */
    protected $donationsData;
    /**
     * @var string
     */
    protected $merchantReference;
    /**
     * @var string
     */
    protected $storeId;
    /**
     * @var string[]
     */
    protected $fields = ['id', 'storeId', 'merchantReference'];

    /**
     * @return DonationsDataModel
     */
    public function getDonationsData(): DonationsDataModel
    {
        return $this->donationsData;
    }

    /**
     * @param DonationsDataModel $donationsData
     */
    public function setDonationsData(DonationsDataModel $donationsData): void
    {
        $this->donationsData = $donationsData;
    }

    /**
     * @return string
     */
    public function getMerchantReference(): string
    {
        return $this->merchantReference;
    }

    /**
     * @param string $merchantReference
     */
    public function setMerchantReference(string $merchantReference): void
    {
        $this->merchantReference = $merchantReference;
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
     * @inheritDoc
     */
    public function getConfig(): EntityConfiguration
    {
        $indexMap = new IndexMap();

        $indexMap->addStringIndex('storeId')
            ->addStringIndex('merchantReference');

        return new EntityConfiguration($indexMap, 'DonationsData');
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $data = parent::toArray();

        $data['storeId'] = $this->storeId;
        $data['merchantReference'] = $this->merchantReference;
        $data['donationsData'] = [
            'merchantReference' => $this->donationsData->getMerchantReference(),
            'donationToken' => $this->donationsData->getDonationToken(),
            'pspReference' => $this->donationsData->getPspReference(),
            'paymentMethod' => $this->donationsData->getPaymentMethod(),
        ];

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function inflate(array $data): void
    {
        parent::inflate($data);

        $donationsData = $data['donationsData'] ?? [];

        $this->donationsData = new DonationsDataModel(
            static::getDataValue($donationsData, 'merchantReference'),
            static::getDataValue($donationsData, 'donationToken'),
            static::getDataValue($donationsData, 'pspReference'),
            static::getDataValue($donationsData, 'paymentMethod')
        );
    }
}
