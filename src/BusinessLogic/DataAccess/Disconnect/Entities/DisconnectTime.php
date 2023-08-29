<?php

namespace Adyen\Core\BusinessLogic\DataAccess\Disconnect\Entities;

use Adyen\Core\Infrastructure\ORM\Configuration\EntityConfiguration;
use Adyen\Core\Infrastructure\ORM\Configuration\IndexMap;
use Adyen\Core\Infrastructure\ORM\Entity;
use Adyen\Core\Infrastructure\ServiceRegister;
use Adyen\Core\Infrastructure\Utility\TimeProvider;
use DateTime;

/**
 * Class DisconnectTime
 *
 * @package Adyen\Core\BusinessLogic\DataAccess\Disconnect\Entities
 */
class DisconnectTime extends Entity
{
    /**
     * Fully qualified name of this class.
     */
    const CLASS_NAME = __CLASS__;
    /**
     * @var string
     */
    protected $storeId;
    /**
     * @var DateTime
     */
    protected $date;
    /**
     * @var string[]
     */
    protected $fields = ['id', 'storeId', 'date'];

    /**
     * @inheritDoc
     */
    public function getConfig(): EntityConfiguration
    {
        $indexMap = new IndexMap();
        $indexMap->addStringIndex('storeId');

        return new EntityConfiguration($indexMap, 'DisconnectTime');
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
     * @return DateTime
     */
    public function getDate(): DateTime
    {
        return $this->date;
    }

    /**
     * @param DateTime $date
     */
    public function setDate(DateTime $date): void
    {
        $this->date = $date;
    }

    public function toArray(): array
    {
        $data = parent::toArray();
        $data['date'] = $this->getDate()->getTimestamp();

        return $data;
    }

    public function inflate(array $data): void
    {
        parent::inflate($data);

        /** @var TimeProvider $timeProvider */
        $timeProvider = ServiceRegister::getService(TimeProvider::CLASS_NAME);
        $this->date = $timeProvider->getDateTime($data['date']);
    }
}
