<?php

namespace Adyen\Core\Tests\BusinessLogic\Domain\NotificationsRemover;

use Adyen\Core\BusinessLogic\DataAccess\Notifications\Entities\Notification;
use Adyen\Core\BusinessLogic\DataAccess\TransactionLog\Entities\TransactionLog;
use Adyen\Core\BusinessLogic\Domain\Connection\Repositories\ConnectionSettingsRepository;
use Adyen\Core\BusinessLogic\Domain\Disconnect\Repositories\DisconnectRepository;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Models\CaptureType;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Models\GeneralSettings;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Services\GeneralSettingsService;
use Adyen\Core\BusinessLogic\Domain\Integration\Order\OrderService;
use Adyen\Core\BusinessLogic\Domain\Integration\ShopNotifications\ShopNotificationChannelAdapter;
use Adyen\Core\BusinessLogic\Domain\Integration\Store\StoreService as IntegrationsStoreService;
use Adyen\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Adyen\Core\BusinessLogic\Domain\NotificationsRemover\Tasks\NotificationsRemoverTask;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Refund\SuccessfulRefundRequestEvent;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Severity;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Repositories\ShopNotificationRepository;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Services\ShopNotificationService;
use Adyen\Core\BusinessLogic\Domain\Stores\Services\StoreService;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Services\TransactionHistoryService;
use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Adyen\Core\BusinessLogic\TransactionLog\Repositories\TransactionLogRepository;
use Adyen\Core\BusinessLogic\TransactionLog\Services\TransactionLogService;
use Adyen\Core\Infrastructure\TaskExecution\QueueItem;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Adyen\Core\Tests\BusinessLogic\Domain\NotificationsRemover\MockComponents\MockShopNotificationService;
use Adyen\Core\Tests\BusinessLogic\Domain\NotificationsRemover\MockComponents\MockStoreService;
use Adyen\Core\Tests\BusinessLogic\Domain\NotificationsRemover\MockComponents\MockTransactionLogService;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\Utility\TestTimeProvider;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;
use DateInterval;
use DateTime;

class NotificationsRemoverTaskTest extends BaseTestCase
{
    /**
     * @var NotificationsRemoverTask
     */
    public $task;
    /**
     * @var GeneralSettingsService
     */
    public $settingsService;
    /**
     * @var ShopNotificationService
     */
    public $notificationsService;
    /**
     * @var TransactionLogService
     */
    public $logsService;
    /**
     * @var MockStoreService
     */
    public $storeService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->storeService = new MockStoreService(
            TestServiceRegister::getService(IntegrationsStoreService::class),
            TestServiceRegister::getService(ConnectionSettingsRepository::class)
        );

        TestServiceRegister::registerService(StoreService::class, function () {
            return $this->storeService;
        });

        $this->task = new NotificationsRemoverTask();
        $this->settingsService = TestServiceRegister::getService(GeneralSettingsService::class);
        $this->notificationsService = new MockShopNotificationService(
            TestServiceRegister::getService(ShopNotificationRepository::class),
            TestServiceRegister::getService(ShopNotificationChannelAdapter::class),
            TestServiceRegister::getService(DisconnectRepository::class)
        );

        TestServiceRegister::registerService(ShopNotificationService::class, function () {
            return $this->notificationsService;
        });

        $this->logsService = new MockTransactionLogService(
            TestServiceRegister::getService(TransactionHistoryService::class),
            TestServiceRegister::getService(TransactionLogRepository::class),
            TestServiceRegister::getService(OrderService::class),
            TestServiceRegister::getService(DisconnectRepository::class)
        );

        TestServiceRegister::registerService(TransactionLogService::class, function () {
            return $this->logsService;
        });
    }

    public function testNoNotifications(): void
    {
        // arrange
        $this->setGeneralSettings();

        // act
        $this->task->execute();

        // assert
        self::assertTrue($this->storeService->methodCalled);
        self::assertTrue($this->notificationsService->hasNotificationsCalled);
        self::assertFalse($this->notificationsService->deleteCalled);
        self::assertTrue($this->logsService->logsExistCalled);
        self::assertFalse($this->logsService->deleteCalled);
    }

    public function testNoOldNotifications(): void
    {
        // arrange
        $this->setGeneralSettings();
        $event = new SuccessfulRefundRequestEvent('1', 'mc');
        StoreContext::doWithStore('store1', function () use ($event) {
            $this->notificationsService->pushNotification($event);
        });
        $log1 = new TransactionLog();
        $log1->setQueueStatus(QueueItem::QUEUED);
        $log1->setAdyenLink('adyenLink');
        $log1->setId(1);
        $log1->setReason('reason1');
        $log1->setMerchantReference('merch1');
        $log1->setPaymentMethod('method');
        $log1->setEventCode('code');
        $log1->setTimestamp((new DateTime())->getTimestamp());
        $log1->setStoreId('store2');
        $log1->setIsSuccessful(true);
        $log1->setExecutionId(1);
        $log1->setShopLink('link1');
        StoreContext::doWithStore(
            'store2',
            function () use ($log1) {
                $this->logsService->save($log1);
            }
        );

        // act
        $this->task->execute();

        // assert
        self::assertTrue($this->storeService->methodCalled);
        self::assertTrue($this->notificationsService->hasNotificationsCalled);
        self::assertFalse($this->notificationsService->deleteCalled);
        self::assertTrue($this->logsService->logsExistCalled);
        self::assertFalse($this->logsService->deleteCalled);
    }

    public function testNoGeneralSettings(): void
    {
        // arrange
        $event = new SuccessfulRefundRequestEvent('1', 'mc');
        StoreContext::doWithStore('store1', function () use ($event) {
            $this->notificationsService->pushNotification($event);
        });
        $log1 = new TransactionLog();
        $log1->setQueueStatus(QueueItem::QUEUED);
        $log1->setAdyenLink('adyenLink');
        $log1->setId(1);
        $log1->setReason('reason1');
        $log1->setMerchantReference('merch1');
        $log1->setPaymentMethod('method');
        $log1->setEventCode('code');
        $log1->setTimestamp((new DateTime())->getTimestamp());
        $log1->setStoreId('store2');
        $log1->setIsSuccessful(true);
        $log1->setExecutionId(1);
        $log1->setShopLink('link1');
        StoreContext::doWithStore(
            'store2',
            function () use ($log1) {
                $this->logsService->save($log1);
            }
        );

        // act
        $this->task->execute();

        // assert
        self::assertTrue($this->storeService->methodCalled);
        self::assertTrue($this->notificationsService->hasNotificationsCalled);
        self::assertFalse($this->notificationsService->deleteCalled);
        self::assertTrue($this->logsService->logsExistCalled);
        self::assertFalse($this->logsService->deleteCalled);
    }

    public function testRemoval(): void
    {
        // arrange
        $this->setGeneralSettings();
        TestTimeProvider::getInstance()->setCurrentLocalTime((new DateTime())->sub(new DateInterval('P100D')));
        $event = new SuccessfulRefundRequestEvent('1', 'mc');
        StoreContext::doWithStore('store1', function () use ($event) {
            $this->notificationsService->pushNotification($event);
        });
        $log1 = new TransactionLog();
        $log1->setQueueStatus(QueueItem::QUEUED);
        $log1->setAdyenLink('adyenLink');
        $log1->setId(1);
        $log1->setReason('reason1');
        $log1->setMerchantReference('merch1');
        $log1->setPaymentMethod('method');
        $log1->setEventCode('code');
        $log1->setTimestamp((new DateTime())->sub(new DateInterval('P100D'))->getTimestamp());
        $log1->setStoreId('store2');
        $log1->setIsSuccessful(true);
        $log1->setExecutionId(1);
        $log1->setShopLink('link1');
        StoreContext::doWithStore(
            'store2',
            function () use ($log1) {
                $this->logsService->save($log1);
            }
        );
        TestTimeProvider::getInstance()->setCurrentLocalTime(new DateTime());

        // act
        $this->task->execute();

        // assert
        self::assertTrue($this->storeService->methodCalled);
        self::assertTrue($this->notificationsService->hasNotificationsCalled);
        self::assertTrue($this->notificationsService->deleteCalled);
        self::assertTrue($this->logsService->logsExistCalled);
        self::assertTrue($this->logsService->deleteCalled);
    }

    protected function setGeneralSettings()
    {
        $generalSettings = new GeneralSettings(false, CaptureType::manual(), 1, '', 70);
        StoreContext::doWithStore(
            'store1',
            [$this->settingsService, 'saveGeneralSettings'],
            [$generalSettings]
        );
        StoreContext::doWithStore(
            'store2',
            [$this->settingsService, 'saveGeneralSettings'],
            [$generalSettings]
        );
    }
}
