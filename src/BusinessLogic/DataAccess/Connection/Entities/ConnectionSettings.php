<?php

namespace Adyen\Core\BusinessLogic\DataAccess\Connection\Entities;

use Adyen\Core\BusinessLogic\Domain\Connection\Models\ApiCredentials;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionSettings as DomainConnectionSettings;
use Adyen\Core\Infrastructure\ORM\Configuration\EntityConfiguration;
use Adyen\Core\Infrastructure\ORM\Configuration\IndexMap;
use Adyen\Core\Infrastructure\ORM\Entity;

/**
 * Class ConnectionSettings
 *
 * @package Adyen\Core\BusinessLogic\DataAccess\Connection\Entities
 */
class ConnectionSettings extends Entity
{
    /**
     * Fully qualified name of this class.
     */
    public const CLASS_NAME = __CLASS__;
    /**
     * @var DomainConnectionSettings
     */
    protected $connectionSettings;

    /**
     * @inheritDoc
     */
    public function inflate(array $data): void
    {
        parent::inflate($data);

        $connectionSettings = $data['connectionSettings'] ?? [];
        $this->connectionSettings = new DomainConnectionSettings(
            $connectionSettings['storeId'] ?? '',
            $connectionSettings['mode'] ?? '',
            isset($connectionSettings['testData']['apiKey'], $connectionSettings['testData']['merchantId']) ?
                new ConnectionData(
                    $connectionSettings['testData']['apiKey'],
                    $connectionSettings['testData']['merchantId'],
                    '',
                    $connectionSettings['testData']['clientKey'] ?? '',
                    $connectionSettings['testData']['apiCredentials'] ? new ApiCredentials(
                        $connectionSettings['testData']['apiCredentials']['id'] ?? '',
                        $connectionSettings['testData']['apiCredentials']['active'] ?? '',
                        $connectionSettings['testData']['apiCredentials']['company'] ?? ''
                    ) : null
                ) : null,
            isset($connectionSettings['liveData']['apiKey'], $connectionSettings['liveData']['merchantId']) ?
                new ConnectionData(
                    $connectionSettings['liveData']['apiKey'],
                    $connectionSettings['liveData']['merchantId'],
                    $connectionSettings['liveData']['clientPrefix'] ?? '',
                    $connectionSettings['liveData']['clientKey'] ?? '',
                    $connectionSettings['liveData']['apiCredentials'] ? new ApiCredentials(
                        $connectionSettings['liveData']['apiCredentials']['id'] ?? '',
                        $connectionSettings['liveData']['apiCredentials']['active'] ?? '',
                        $connectionSettings['liveData']['apiCredentials']['company'] ?? ''
                    ) : null
                ) : null
        );
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $data = parent::toArray();

        $data['connectionSettings'] = [
            'storeId' => $this->connectionSettings->getStoreId(),
            'mode' => $this->connectionSettings->getMode(),
            'testData' => $this->connectionSettings->getTestData() ? [
                'apiKey' => $this->connectionSettings->getTestData()->getApiKey(),
                'merchantId' => $this->connectionSettings->getTestData()->getMerchantId(),
                'clientKey' => $this->connectionSettings->getTestData()->getClientKey(),
                'apiCredentials' => $this->connectionSettings->getTestData()->getApiCredentials() ? [
                    'id' => $this->connectionSettings->getTestData()->getApiCredentials()->getId(),
                    'active' => $this->connectionSettings->getTestData()->getApiCredentials()->isActive(),
                    'company' => $this->connectionSettings->getTestData()->getApiCredentials()->getCompany(),
                ] : [],
            ] : [],
            'liveData' => $this->connectionSettings->getLiveData() ? [
                'apiKey' => $this->connectionSettings->getLiveData()->getApiKey(),
                'merchantId' => $this->connectionSettings->getLiveData()->getMerchantId(),
                'clientPrefix' => $this->connectionSettings->getLiveData()->getClientPrefix(),
                'clientKey' => $this->connectionSettings->getLiveData()->getClientKey(),
                'apiCredentials' => $this->connectionSettings->getLiveData()->getApiCredentials() ?
                    [
                        'id' => $this->connectionSettings->getLiveData()->getApiCredentials()->getId(),
                        'active' => $this->connectionSettings->getLiveData()->getApiCredentials()->isActive(),
                        'company' => $this->connectionSettings->getLiveData()->getApiCredentials()->getCompany(),
                    ] : [],
            ] : [],
        ];

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function getConfig(): EntityConfiguration
    {
        $indexMap = new IndexMap();

        $indexMap->addStringIndex('storeId');

        return new EntityConfiguration($indexMap, 'ConnectionSettings');
    }

    /**
     * @return string
     */
    public function getStoreId(): string
    {
        return $this->connectionSettings->getStoreId();
    }

    /**
     * @return DomainConnectionSettings
     */
    public function getConnectionSettings(): DomainConnectionSettings
    {
        return $this->connectionSettings;
    }

    /**
     * @param DomainConnectionSettings $connectionSettings
     */
    public function setConnectionSettings(DomainConnectionSettings $connectionSettings): void
    {
        $this->connectionSettings = $connectionSettings;
    }
}
