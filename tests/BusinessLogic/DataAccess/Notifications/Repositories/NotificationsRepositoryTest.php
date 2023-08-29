<?php

namespace Adyen\Core\Tests\BusinessLogic\DataAccess\Notifications\Repositories;

use Adyen\Core\BusinessLogic\DataAccess\Notifications\Entities\Notification;
use Adyen\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Cancellation\SuccessfulCancellationRequestEvent;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Capture\SuccessfulCaptureRequestEvent;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Capture\FailedCaptureEvent;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Capture\SuccessfulCaptureEvent;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Refund\FailedRefundEvent;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Refund\SuccessfulRefundRequestEvent;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Refund\SuccessfulRefundEvent;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Repositories\ShopNotificationRepository;
use Adyen\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use Adyen\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;
use Exception;

/**
 * Class NotificationsRepositoryTest
 *
 * @package Adyen\Core\Tests\BusinessLogic\DataAccess\Notifications\Repositories
 */
class NotificationsRepositoryTest extends BaseTestCase
{
    /**
     * @var RepositoryInterface
     */
    private $repository;

    /**
     * @var ShopNotificationRepository
     */
    private $notificationRepository;

    /**
     * @throws RepositoryNotRegisteredException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = TestRepositoryRegistry::getRepository(Notification::getClassName());
        $this->notificationRepository = TestServiceRegister::getService(ShopNotificationRepository::class);
    }

    /**
     * @throws Exception
     */
    public function testGetSettingsNoSettings(): void
    {
        // act
        $result = StoreContext::doWithStore('1', [$this->notificationRepository, 'getNotifications'], [10, 1]);

        // assert
        self::assertEmpty($result);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testPushNotification(): void
    {
        // arrange
        $event = new SuccessfulCaptureEvent('1', 'method');

        // act
        StoreContext::doWithStore('1', [$this->notificationRepository, 'pushNotification'], [$event]);

        // assert
        /** @var Notification $savedEntity */
        $savedEntity = $this->repository->selectOne();
        self::assertEquals($event->getOrderId(), $savedEntity->getOrderID());
        self::assertEquals($event->getSeverity()->getSeverity(), $savedEntity->getSeverity());
        self::assertEquals($event->getDetails(), $savedEntity->getDetails());
        self::assertEquals($event->getMessage(), $savedEntity->getMessage());
        self::assertEquals($event->getPaymentMethod(), $savedEntity->getPaymentMethod());
        self::assertEquals($event->getDateAndTime()->getTimestamp(), $savedEntity->getTimestamp());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetNotifications(): void
    {
        // arrange
        $event1 = new SuccessfulCaptureEvent('1', 'method');
        $event2 = new FailedCaptureEvent('1', 'method');
        $event3 = new SuccessfulCaptureRequestEvent('1', 'method');
        $event4 = new SuccessfulCancellationRequestEvent('1', 'method');
        $event5 = new FailedCaptureEvent('1', 'method');
        $event6 = new SuccessfulCaptureEvent('1', 'method');
        $event7 = new SuccessfulRefundRequestEvent('1', 'method');
        $event8 = new SuccessfulRefundEvent('1', 'method');
        $event9 = new FailedRefundEvent('1', 'method');

        // act
        StoreContext::doWithStore('1', [$this->notificationRepository, 'pushNotification'], [$event1]);
        StoreContext::doWithStore('1', [$this->notificationRepository, 'pushNotification'], [$event2]);
        StoreContext::doWithStore('1', [$this->notificationRepository, 'pushNotification'], [$event3]);
        StoreContext::doWithStore('1', [$this->notificationRepository, 'pushNotification'], [$event4]);
        StoreContext::doWithStore('1', [$this->notificationRepository, 'pushNotification'], [$event5]);
        StoreContext::doWithStore('1', [$this->notificationRepository, 'pushNotification'], [$event6]);
        StoreContext::doWithStore('1', [$this->notificationRepository, 'pushNotification'], [$event7]);
        StoreContext::doWithStore('1', [$this->notificationRepository, 'pushNotification'], [$event8]);
        StoreContext::doWithStore('1', [$this->notificationRepository, 'pushNotification'], [$event9]);
        $result = StoreContext::doWithStore('1', [$this->notificationRepository, 'getNotifications'], [10, 0]);

        // assert
        self::assertCount(9, $result);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetNotificationsForDifferentStore(): void
    {
        // arrange
        $event1 = new SuccessfulCaptureEvent('1', 'method');
        $event2 = new FailedCaptureEvent('1', 'method');
        $event3 = new SuccessfulCaptureRequestEvent('1', 'method');
        $event4 = new SuccessfulCancellationRequestEvent('1', 'method');
        $event5 = new FailedCaptureEvent('1', 'method');
        $event6 = new SuccessfulCaptureEvent('1', 'method');
        $event7 = new SuccessfulRefundRequestEvent('1', 'method');
        $event8 = new SuccessfulRefundEvent('1', 'method');
        $event9 = new FailedRefundEvent('1', 'method');

        // act
        StoreContext::doWithStore('1', [$this->notificationRepository, 'pushNotification'], [$event1]);
        StoreContext::doWithStore('1', [$this->notificationRepository, 'pushNotification'], [$event2]);
        StoreContext::doWithStore('1', [$this->notificationRepository, 'pushNotification'], [$event3]);
        StoreContext::doWithStore('1', [$this->notificationRepository, 'pushNotification'], [$event4]);
        StoreContext::doWithStore('1', [$this->notificationRepository, 'pushNotification'], [$event5]);
        StoreContext::doWithStore('1', [$this->notificationRepository, 'pushNotification'], [$event6]);
        StoreContext::doWithStore('1', [$this->notificationRepository, 'pushNotification'], [$event7]);
        StoreContext::doWithStore('1', [$this->notificationRepository, 'pushNotification'], [$event8]);
        StoreContext::doWithStore('1', [$this->notificationRepository, 'pushNotification'], [$event9]);
        $result = StoreContext::doWithStore('2', [$this->notificationRepository, 'getNotifications'], [10, 0]);

        // assert
        self::assertCount(0, $result);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testCount(): void
    {
        // arrange
        $event1 = new SuccessfulCaptureEvent('1', 'method');
        $event2 = new FailedCaptureEvent('1', 'method');
        $event3 = new SuccessfulCaptureRequestEvent('1', 'method');
        $event4 = new SuccessfulCancellationRequestEvent('1', 'method');
        $event5 = new FailedCaptureEvent('1', 'method');
        $event6 = new SuccessfulCaptureEvent('1', 'method');
        $event7 = new SuccessfulRefundRequestEvent('1', 'method');
        $event8 = new SuccessfulRefundEvent('1', 'method');
        $event9 = new FailedRefundEvent('1', 'method');

        // act
        StoreContext::doWithStore('1', [$this->notificationRepository, 'pushNotification'], [$event1]);
        StoreContext::doWithStore('1', [$this->notificationRepository, 'pushNotification'], [$event2]);
        StoreContext::doWithStore('1', [$this->notificationRepository, 'pushNotification'], [$event3]);
        StoreContext::doWithStore('1', [$this->notificationRepository, 'pushNotification'], [$event4]);
        StoreContext::doWithStore('1', [$this->notificationRepository, 'pushNotification'], [$event5]);
        StoreContext::doWithStore('1', [$this->notificationRepository, 'pushNotification'], [$event6]);
        StoreContext::doWithStore('1', [$this->notificationRepository, 'pushNotification'], [$event7]);
        StoreContext::doWithStore('1', [$this->notificationRepository, 'pushNotification'], [$event8]);
        StoreContext::doWithStore('1', [$this->notificationRepository, 'pushNotification'], [$event9]);
        $result = StoreContext::doWithStore('1', [$this->notificationRepository, 'count']);

        // assert
        self::assertEquals(9, $result);
    }
}
