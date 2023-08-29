<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\Connection\Response;

use Adyen\Core\BusinessLogic\AdminAPI\Response\Response;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionSettings;

/**
 * Class ConnectionSettingsResponse
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\Connection\Response
 */
class ConnectionSettingsResponse extends Response
{
    /**
     * @var ConnectionSettings
     */
    private $connectionSettings;

    /**
     * @param ConnectionSettings|null $connectionSettings
     */
    public function __construct(?ConnectionSettings $connectionSettings)
    {
        $this->connectionSettings = $connectionSettings;
    }

    public function toArray(): array
    {
        if (!$this->connectionSettings) {
            return [];
        }

        return [
            'mode' => $this->connectionSettings->getMode(),
            'testData' => [
                'apiKey' => $this->connectionSettings->getTestData() ?
                    $this->connectionSettings->getTestData()->getApiKey() : '',
                'merchantId' => $this->connectionSettings->getTestData() ?
                    $this->connectionSettings->getTestData()->getMerchantId() : '',
            ],
            'liveData' => [
                'apiKey' => $this->connectionSettings->getLiveData() ?
                    $this->connectionSettings->getLiveData()->getApiKey() : '',
                'merchantId' => $this->connectionSettings->getLiveData() ?
                    $this->connectionSettings->getLiveData()->getMerchantId() : '',
            ]
        ];
    }
}
