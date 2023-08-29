<?php

namespace Adyen\Core\Tests\BusinessLogic\AdminAPI\ShopNotifications;

use Adyen\Core\BusinessLogic\AdminAPI\AdminAPI;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Capture\SuccessfulCaptureEvent;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;

/**
 * Class ShopNotificationsApiTest
 *
 * @package Adyen\Core\Tests\BusinessLogic\AdminAPI\ShopNotifications
 */
class ShopNotificationsApiTest extends BaseTestCase
{
    /**
     * @return void
     */
    public function testResponseSuccessful(): void
    {
        // Act
        $notifications = AdminAPI::get()->shopNotifications('1')->getNotifications(0, 10);

        // Assert
        self::assertTrue($notifications->isSuccessful());
    }

    /**
     * @return void
     */
    public function testPushNotifications(): void
    {
        $event1 = new SuccessfulCaptureEvent('1', 'method');

        // Act
        AdminAPI::get()->shopNotifications('1')->pushNotification($event1);
        $notifications = AdminAPI::get()->shopNotifications('1')->getNotifications(1, 3);

        $notificationsArray = $notifications->toArray();

        // Assert
        self::assertTrue($notifications->isSuccessful());
        self::assertFalse($notificationsArray['nextPageAvailable']);
        self::assertEquals('1', $notificationsArray['notifications'][0]['orderId']);
        self::assertEquals('method', $notificationsArray['notifications'][0]['paymentMethod']);
        self::assertEquals('info', $notificationsArray['notifications'][0]['severity']);
    }
}
