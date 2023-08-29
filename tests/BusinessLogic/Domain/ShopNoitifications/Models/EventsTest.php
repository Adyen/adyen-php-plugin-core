<?php

namespace Adyen\Core\Tests\BusinessLogic\Domain\ShopNoitifications\Models;

use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Cancellation\FailedCancellationRequestEvent;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Cancellation\SuccessfulCancellationRequestEvent;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Capture\FailedCaptureRequestEvent;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Capture\SuccessfulCaptureRequestEvent;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Capture\FailedCaptureEvent;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Capture\SuccessfulCaptureEvent;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Refund\FailedRefundEvent;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Refund\FailedRefundRequestEvent;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Refund\SuccessfulRefundRequestEvent;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Refund\SuccessfulRefundEvent;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Severity;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;

/**
 * Class EventsTest
 *
 * @package Adyen\Core\Tests\BusinessLogic\Domain\ShopNoitifications\Models
 */
class EventsTest extends BaseTestCase
{

    /**
     * @return void
     */
    public function testCreationOfEvents(): void
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
        $event10 = new FailedCancellationRequestEvent('1', 'method');
        $event11 = new FailedRefundRequestEvent('1', 'method');
        $event12 = new FailedCaptureRequestEvent('1', 'method');

        // act

        // assert
        self::assertEquals($event1->getSeverity()->getSeverity(), Severity::info()->getSeverity());
        self::assertEquals($event2->getSeverity()->getSeverity(), Severity::warning()->getSeverity());
        self::assertEquals($event3->getSeverity()->getSeverity(), Severity::info()->getSeverity());
        self::assertEquals($event4->getSeverity()->getSeverity(), Severity::info()->getSeverity());
        self::assertEquals($event5->getSeverity()->getSeverity(), Severity::warning()->getSeverity());
        self::assertEquals($event6->getSeverity()->getSeverity(), Severity::info()->getSeverity());
        self::assertEquals($event7->getSeverity()->getSeverity(), Severity::info()->getSeverity());
        self::assertEquals($event8->getSeverity()->getSeverity(), Severity::info()->getSeverity());
        self::assertEquals($event9->getSeverity()->getSeverity(), Severity::warning()->getSeverity());
        self::assertEquals($event10->getSeverity()->getSeverity(), Severity::error()->getSeverity());
        self::assertEquals($event11->getSeverity()->getSeverity(), Severity::error()->getSeverity());
        self::assertEquals($event12->getSeverity()->getSeverity(), Severity::error()->getSeverity());
    }
}
