<?php

namespace Adyen\Core\BusinessLogic\Domain\Disconnect\Tasks;

use Adyen\Core\BusinessLogic\Domain\Checkout\AdyenGiving\Repositories\DonationsDataRepository;
use Adyen\Core\BusinessLogic\Domain\Disconnect\Repositories\DisconnectRepository;
use Adyen\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Severity;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Services\ShopNotificationService;
use Adyen\Core\BusinessLogic\TransactionLog\Services\TransactionLogService;
use Adyen\Core\Infrastructure\Serializer\Serializer;
use Adyen\Core\Infrastructure\ServiceRegister;
use Adyen\Core\Infrastructure\TaskExecution\Task;
use DateTime;
use Exception;

/**
 * Class DisconnectTask
 *
 * @package Adyen\Core\BusinessLogic\Domain\Disconnect\Tasks
 */
class DisconnectTask extends Task
{
    /**
     * @var string
     */
    private $storeId;
    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @param string $storeId
     * @param DateTime $dateTime
     */
    public function __construct(string $storeId, DateTime $dateTime)
    {
        $this->storeId = $storeId;
        $this->dateTime = $dateTime;
    }

    /**
     * @inheritDoc
     */
    public static function fromArray(array $array): DisconnectTask
    {
        return new static(
            $array['storeId'],
            (new DateTime())->setTimestamp($array['date'])
        );
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'storeId' => $this->storeId,
            'date' => $this->dateTime->getTimestamp(),
        ];
    }

    /**
     * @inheritDoc
     */
    public function serialize(): ?string
    {
        return Serializer::serialize($this->toArray());
    }

    /**
     * @inheritDoc
     */
    public function unserialize($serialized): void
    {
        $unserialized = Serializer::unserialize($serialized);
        $this->storeId = $unserialized['storeId'];
        $this->dateTime = (new DateTime())->setTimestamp($unserialized['date']);
    }

    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    public function execute(): void
    {
        StoreContext::doWithStore($this->storeId, function () {
           $this->doExecute();
        });
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    protected function doExecute(): void
    {
        $this->deleteShopNotifications();
        $this->reportProgress(45);
        $this->deleteWebhookNotifications();
        $this->reportProgress(90);
        $this->deleteDonationsData();
        $this->reportProgress(95);
        $this->getDisconnectRepository()->deleteDisconnectTime();
        $this->reportProgress(100);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    protected function deleteShopNotifications(): void
    {
        $notificationService = $this->getShopNotificationsService();

        while ($notificationService->hasSignificantNotifications(
            $this->dateTime,
            [Severity::ERROR, Severity::WARNING, Severity::INFO]
        )) {
            $notificationService->deleteNotifications($this->dateTime, 5000);

            $this->reportAlive();
        }
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    protected function deleteWebhookNotifications(): void
    {
        $logService = $this->getLogService();

        while ($logService->logsExist($this->dateTime)) {
            $logService->deleteLogs($this->dateTime, 5000);

            $this->reportAlive();
        }
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    protected function deleteDonationsData(): void
    {
        $repository = $this->getDonationsDataRepository();

        while ($repository->donationDataExists()) {
            $repository->delete();

            $this->reportAlive();
        }
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
     * @return DonationsDataRepository
     */
    protected function getDonationsDataRepository(): DonationsDataRepository
    {
        return ServiceRegister::getService(DonationsDataRepository::class);
    }

    /**
     * @return DisconnectRepository
     */
    protected function getDisconnectRepository(): DisconnectRepository
    {
        return ServiceRegister::getService(DisconnectRepository::class);
    }
}
