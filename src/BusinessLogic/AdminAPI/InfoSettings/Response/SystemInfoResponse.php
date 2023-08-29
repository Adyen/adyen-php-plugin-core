<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\InfoSettings\Response;

use Adyen\Core\BusinessLogic\AdminAPI\Response\Response;
use Adyen\Core\BusinessLogic\DataAccess\Connection\Entities\ConnectionSettings;
use Adyen\Core\BusinessLogic\DataAccess\Payment\Entities\PaymentMethod;
use Adyen\Core\BusinessLogic\Domain\InfoSettings\Models\SystemInfo;
use Adyen\Core\Infrastructure\TaskExecution\QueueItem;

/**
 * Class SystemInfoResponse
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\InfoSettings\Response
 */
class SystemInfoResponse extends Response
{
    /**
     * @var string
     */
    private $phpInfo;

    /**
     * @var SystemInfo
     */
    private $systemInfo;

    /**
     * @var PaymentMethod[]
     */
    private $paymentMethods;

    /**
     * @var QueueItem[]
     */
    private $queueItems;

    /**
     * @var ConnectionSettings[]
     */
    private $connectionItems;

    /**
     * @var string
     */
    private $webhookValidation;

    /**
     * @param string $phpInfo
     * @param SystemInfo $systemInfo
     * @param array $paymentMethods
     * @param array $queueItems
     * @param array $connectionItems
     * @param string $webhookValidation
     */
    public function __construct(
        string $phpInfo,
        SystemInfo $systemInfo,
        array $paymentMethods,
        array $queueItems,
        array $connectionItems,
        string $webhookValidation
    ) {
        $this->phpInfo = $phpInfo;
        $this->systemInfo = $systemInfo;
        $this->paymentMethods = $paymentMethods;
        $this->queueItems = $queueItems;
        $this->connectionItems = $connectionItems;
        $this->webhookValidation = $webhookValidation;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'phpInfo' => $this->phpInfo,
            'systemInfo' => [
                'systemVersion' => $this->systemInfo->getSystemVersion(),
                'pluginVersion' => $this->systemInfo->getPluginVersion(),
                'mainThemeName' => $this->systemInfo->getMainThemeName(),
                'shopUrl' => $this->systemInfo->getShopUrl(),
                'adminUrl' => $this->systemInfo->getAdminUrl(),
                'asyncProcessUrl' => $this->systemInfo->getAsyncProcessUrl(),
                'databaseName' => $this->systemInfo->getDatabaseName(),
                'databaseVersion' => $this->systemInfo->getDatabaseVersion()
            ],
            'paymentMethods' => $this->paymentMethodsToArray(),
            'queueItems' => $this->queueItemsToArray(),
            'connectionSettings' => $this->connectionItemsToArray(),
            'webhookValidation' => $this->webhookValidation
        ];
    }

    /**
     * @return array
     */
    private function queueItemsToArray(): array
    {
        $items = [];

        foreach ($this->queueItems as $item) {
            $items[] = $item->toArray();
        }

        return $items;
    }

    /**
     * @return array
     */
    private function connectionItemsToArray(): array
    {
        $items = [];

        foreach ($this->connectionItems as $item) {
            $newItem = $item->toArray();

            if (!empty($newItem['connectionSettings']['testData'])) {
                $newItem['connectionSettings']['testData']['apiKey'] = '***';
            }

            if (!empty($newItem['connectionSettings']['liveData'])) {
                $newItem['connectionSettings']['liveData']['apiKey'] = '***';
            }

            $items[] = $newItem;
        }

        return $items;
    }

    /**
     * @return array
     */
    private function paymentMethodsToArray(): array
    {
        $methods = [];

        foreach ($this->paymentMethods as $method) {
            $methods[] = $method->toArray();
        }

        return $methods;
    }
}
