<?php

namespace Adyen\Core\BusinessLogic\Domain\NotificationsRemover\Tasks;

use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Services\GeneralSettingsService;
use Adyen\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Severity;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Services\ShopNotificationService;
use Adyen\Core\BusinessLogic\Domain\Stores\Exceptions\FailedToRetrieveStoresException;
use Adyen\Core\BusinessLogic\Domain\Stores\Models\Store;
use Adyen\Core\BusinessLogic\Domain\Stores\Services\StoreService;
use Adyen\Core\BusinessLogic\TransactionLog\Services\TransactionLogService;
use Adyen\Core\Infrastructure\ServiceRegister;
use Adyen\Core\Infrastructure\TaskExecution\Task;
use DateInterval;
use DateTime;
use Exception;

/**
 * Class NotificationsRemoverTask
 *
 * @package dyen\Core\BusinessLogic\Domain\NotificationsRemover\Tasks
 */
class NotificationsRemoverTask extends Task
{
    private const DEFAULT_RETENTION_PERIOD = 60;

    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    public function execute(): void
    {
        $stores = $this->getStores();
        $this->reportProgress(5);

        foreach ($stores as $key => $store) {
            StoreContext::doWithStore(
                $store->getStoreId(),
                function () {
                    $this->doExecute();
                }
            );

            $this->reportProgress(5 + $key * 90 / count($stores));
        }

        $this->reportProgress(100);
    }

    /**
     * @throws Exception
     */
    protected function doExecute(): void
    {
        $settings = $this->getSettingsService()->getGeneralSettings();
        $captureDelay = $settings !== null ? $settings->getRetentionPeriod() : self::DEFAULT_RETENTION_PERIOD;
        $date = (new DateTime())->sub(new DateInterval('P' . $captureDelay . 'D'));

        $this->deleteShopNotifications($date);
        $this->deleteWebhookNotifications($date);
    }

    /**
     * @param DateTime $dateTime
     *
     * @return void
     *
     * @throws Exception
     */
    protected function deleteShopNotifications(DateTime $dateTime): void
    {
        $notificationService = $this->getShopNotificationsService();

        while ($notificationService->hasSignificantNotifications(
            $dateTime,
            [Severity::ERROR, Severity::WARNING, Severity::INFO]
        )) {
            $notificationService->deleteNotifications($dateTime, 5000);

            $this->reportAlive();
        }
    }

    /**
     * @param DateTime $dateTime
     *
     * @return void
     *
     * @throws Exception
     */
    protected function deleteWebhookNotifications(DateTime $dateTime): void
    {
        $logService = $this->getLogService();

        while ($logService->logsExist($dateTime)) {
            $logService->deleteLogs($dateTime, 5000);

            $this->reportAlive();
        }
    }

    /**
     * @return Store[]
     *
     * @throws FailedToRetrieveStoresException
     */
    protected function getStores(): array
    {
        return $this->getStoreService()->getStores();
    }

    /**
     * @return GeneralSettingsService
     */
    protected function getSettingsService(): GeneralSettingsService
    {
        return ServiceRegister::getService(GeneralSettingsService::class);
    }

    /**
     * @return ShopNotificationService
     */
    protected function getShopNotificationsService(): ShopNotificationService
    {
        return ServiceRegister::getService(ShopNotificationService::class);
    }

    /**
     * @return TransactionLogService
     */
    protected function getLogService(): TransactionLogService
    {
        return ServiceRegister::getService(TransactionLogService::class);
    }

    /**
     * @return StoreService
     */
    protected function getStoreService(): StoreService
    {
        return ServiceRegister::getService(StoreService::class);
    }
}
