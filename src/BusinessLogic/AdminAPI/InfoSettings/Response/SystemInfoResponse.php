<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\InfoSettings\Response;

use Adyen\Core\BusinessLogic\AdminAPI\Response\Response;
use Adyen\Core\BusinessLogic\DataAccess\Connection\Entities\ConnectionSettings;
use Adyen\Core\BusinessLogic\DataAccess\Payment\Entities\PaymentMethod;
use Adyen\Core\BusinessLogic\DataAccess\TransactionLog\Entities\TransactionLog;
use Adyen\Core\BusinessLogic\Domain\InfoSettings\Models\SystemInfo;
use Adyen\Core\Infrastructure\TaskExecution\QueueItem;
use Adyen\Core\Infrastructure\Utility\TimeProvider;
use DateTimeInterface;

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
     * @var TransactionLog[]
     */
    private $transactionLogs;

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
     * @param array $transactionLogs
     * @param array $connectionItems
     * @param string $webhookValidation
     */
    public function __construct(
        string $phpInfo,
        SystemInfo $systemInfo,
        array $paymentMethods,
        array $queueItems,
        array $transactionLogs,
        array $connectionItems,
        string $webhookValidation
    ) {
        $this->phpInfo = $phpInfo;
        $this->systemInfo = $systemInfo;
        $this->paymentMethods = $paymentMethods;
        $this->queueItems = $queueItems;
        $this->transactionLogs = $transactionLogs;
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
            'transactionLogs' => $this->logsToArray(),
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

    private function logsToArray(): array
    {
        $logsToArray = [];

        foreach ($this->transactionLogs as $log) {
            $logsToArray[] = [
                'orderId' => $log->getMerchantReference(),
                'paymentMethod' => $log->getPaymentMethod(),
                'notificationId' => $log->getId(),
                'dateAndTime' => TimeProvider::getInstance()
                    ->getDateTime($log->getTimestamp())
                    ->format(DateTimeInterface::ATOM),
                'code' => $log->getEventCode(),
                'successful' => $log->isSuccessful(),
                'status' => $log->getQueueStatus(),
                'hasDetails' => !(empty($log->getReason()))
                    || !(empty($log->getFailureDescription()))
                    || !(empty($log->getAdyenLink()))
                    || !(empty($log->getShopLink())),
                'details' => [
                    'reason' => $log->getReason() ?? '',
                    'failureDescription' => $log->getFailureDescription() ?? '',
                    'adyenLink' => $log->getAdyenLink(),
                    'shopLink' => $log->getShopLink()
                ],
                'logo' => $this->getLogo($log->getPaymentMethod())
            ];
        }

        return $logsToArray;
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
