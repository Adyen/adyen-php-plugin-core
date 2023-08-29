<?php

namespace Adyen\Core\Tests\BusinessLogic\Webhook\Tasks;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Currency;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Services\ShopNotificationService;
use Adyen\Core\BusinessLogic\Domain\Webhook\Models\Webhook;
use Adyen\Core\BusinessLogic\Webhook\Tasks\OrderUpdateTask;
use Adyen\Core\Infrastructure\ServiceRegister;
use Adyen\Core\Infrastructure\TaskExecution\Exceptions\AbortTaskExecutionException;
use Adyen\Core\Tests\BusinessLogic\Common\BaseSerializationTestCase;
use Adyen\Webhook\EventCodes;

/**
 * Class OrderUpdateTaskTest
 *
 * @package Adyen\Core\Tests\BusinessLogic\Webhook\Tasks
 */
class OrderUpdateTaskTest extends BaseSerializationTestCase
{
    /**
     * @var Webhook
     */
    private $webhook;

    /**
     * @var ShopNotificationService
     */
    private $shopNotificationService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->webhook = new Webhook(
            Amount::fromInt(1111, Currency::getDefault()),
            EventCodes::AUTHORISATION,
            '2023-02-01T14:09:24+01:00',
            '123',
            'code',
            'reference',
            'pspReference',
            'paymentMethod',
            'reason',
            true,
            'originalRef',
            0,
            false
        );

        $this->serializable = new OrderUpdateTask($this->webhook);
        $this->shopNotificationService = ServiceRegister::getService(ShopNotificationService::class);
    }

    /**
     * @return void
     *
     * @throws AbortTaskExecutionException
     */
    public function testPushingSuccessfulAuthorizedNotification(): void
    {
        // arrange
        $task = new OrderUpdateTask($this->webhook);

        // act
        $task->execute();

        // assert
        $notifications = $this->shopNotificationService->getNotifications(1, 0);

        self::assertNotEmpty($notifications);
        self::assertEquals($notifications[0]->getPaymentMethod(), $this->webhook->getPaymentMethod());
        self::assertEquals($notifications[0]->getOrderId(), $this->webhook->getMerchantReference());
        self::assertEquals('info', $notifications[0]->getSeverity());
    }

    /**
     * @return void
     *
     * @throws AbortTaskExecutionException
     */
    public function testPushingFailedAuthorizedNotification(): void
    {
        // arrange
        $this->webhook = new Webhook(
            Amount::fromInt(1111, Currency::getDefault()),
            EventCodes::AUTHORISATION,
            '2023-02-01T14:09:24+01:00',
            '123',
            'code',
            'reference',
            'pspReference',
            'paymentMethod',
            'reason',
            false,
            'originalRef',
            0,
            false
        );
        $task = new OrderUpdateTask($this->webhook);

        // act
        $task->execute();

        // assert
        $notifications = $this->shopNotificationService->getNotifications(1, 0);

        self::assertNotEmpty($notifications);
        self::assertEquals($notifications[0]->getPaymentMethod(), $this->webhook->getPaymentMethod());
        self::assertEquals($notifications[0]->getOrderId(), $this->webhook->getMerchantReference());
        self::assertEquals('warning', $notifications[0]->getSeverity());
    }

    /**
     * @return void
     *
     * @throws AbortTaskExecutionException
     */
    public function testPushingSuccessfulCancellationNotification(): void
    {
        // arrange
        $this->webhook = new Webhook(
            Amount::fromInt(1111, Currency::getDefault()),
            EventCodes::CANCELLATION,
            '2023-02-01T14:09:24+01:00',
            '123',
            'code',
            'reference',
            'pspReference',
            'paymentMethod',
            'reason',
            true,
            'originalRef',
            0,
            false
        );
        $task = new OrderUpdateTask($this->webhook);

        // act
        $task->execute();

        // assert
        $notifications = $this->shopNotificationService->getNotifications(1, 0);

        self::assertNotEmpty($notifications);
        self::assertEquals($notifications[0]->getPaymentMethod(), $this->webhook->getPaymentMethod());
        self::assertEquals($notifications[0]->getOrderId(), $this->webhook->getMerchantReference());
        self::assertEquals('info', $notifications[0]->getSeverity());
    }

    /**
     * @return void
     *
     * @throws AbortTaskExecutionException
     */
    public function testPushingFailedCancellationNotification(): void
    {
        // arrange
        $this->webhook = new Webhook(
            Amount::fromInt(1111, Currency::getDefault()),
            EventCodes::CANCELLATION,
            '2023-02-01T14:09:24+01:00',
            '123',
            'code',
            'reference',
            'pspReference',
            'paymentMethod',
            'reason',
            false,
            'originalRef',
            0,
            false
        );
        $task = new OrderUpdateTask($this->webhook);

        // act
        $task->execute();

        // assert
        $notifications = $this->shopNotificationService->getNotifications(1, 0);

        self::assertNotEmpty($notifications);
        self::assertEquals($notifications[0]->getPaymentMethod(), $this->webhook->getPaymentMethod());
        self::assertEquals($notifications[0]->getOrderId(), $this->webhook->getMerchantReference());
        self::assertEquals('warning', $notifications[0]->getSeverity());
    }

    /**
     * @return void
     *
     * @throws AbortTaskExecutionException
     */
    public function testPushingSuccessfulCaptureNotification(): void
    {
        // arrange
        $this->webhook = new Webhook(
            Amount::fromInt(1111, Currency::getDefault()),
            EventCodes::CAPTURE,
            '2023-02-01T14:09:24+01:00',
            '123',
            'code',
            'reference',
            'pspReference',
            'paymentMethod',
            'reason',
            true,
            'originalRef',
            0,
            false
        );
        $task = new OrderUpdateTask($this->webhook);

        // act
        $task->execute();

        // assert
        $notifications = $this->shopNotificationService->getNotifications(1, 0);

        self::assertNotEmpty($notifications);
        self::assertEquals($notifications[0]->getPaymentMethod(), $this->webhook->getPaymentMethod());
        self::assertEquals($notifications[0]->getOrderId(), $this->webhook->getMerchantReference());
        self::assertEquals('info', $notifications[0]->getSeverity());
    }

    /**
     * @return void
     *
     * @throws AbortTaskExecutionException
     */
    public function testPushingFailedCaptureNotification(): void
    {
        // arrange
        $this->webhook = new Webhook(
            Amount::fromInt(1111, Currency::getDefault()),
            EventCodes::CAPTURE,
            '2023-02-01T14:09:24+01:00',
            '123',
            'code',
            'reference',
            'pspReference',
            'paymentMethod',
            'reason',
            false,
            'originalRef',
            0,
            false
        );
        $task = new OrderUpdateTask($this->webhook);

        // act
        $task->execute();

        // assert
        $notifications = $this->shopNotificationService->getNotifications(1, 0);

        self::assertNotEmpty($notifications);
        self::assertEquals($notifications[0]->getPaymentMethod(), $this->webhook->getPaymentMethod());
        self::assertEquals($notifications[0]->getOrderId(), $this->webhook->getMerchantReference());
        self::assertEquals('warning', $notifications[0]->getSeverity());
    }


    /**
     * @return void
     *
     * @throws AbortTaskExecutionException
     */
    public function testPushingSuccessfulRefundNotification(): void
    {
        // arrange
        $this->webhook = new Webhook(
            Amount::fromInt(1111, Currency::getDefault()),
            EventCodes::REFUND,
            '2023-02-01T14:09:24+01:00',
            '123',
            'code',
            'reference',
            'pspReference',
            'paymentMethod',
            'reason',
            true,
            'originalRef',
            0,
            false
        );
        $task = new OrderUpdateTask($this->webhook);

        // act
        $task->execute();

        // assert
        $notifications = $this->shopNotificationService->getNotifications(1, 0);

        self::assertNotEmpty($notifications);
        self::assertEquals($notifications[0]->getPaymentMethod(), $this->webhook->getPaymentMethod());
        self::assertEquals($notifications[0]->getOrderId(), $this->webhook->getMerchantReference());
        self::assertEquals('info', $notifications[0]->getSeverity());
    }

    /**
     * @return void
     *
     * @throws AbortTaskExecutionException
     */
    public function testPushingFailedRefundNotification(): void
    {
        // arrange
        $this->webhook = new Webhook(
            Amount::fromInt(1111, Currency::getDefault()),
            EventCodes::REFUND,
            '2023-02-01T14:09:24+01:00',
            '123',
            'code',
            'reference',
            'pspReference',
            'paymentMethod',
            'reason',
            false,
            'originalRef',
            0,
            false
        );
        $task = new OrderUpdateTask($this->webhook);

        // act
        $task->execute();

        // assert
        $notifications = $this->shopNotificationService->getNotifications(1, 0);

        self::assertNotEmpty($notifications);
        self::assertEquals($notifications[0]->getPaymentMethod(), $this->webhook->getPaymentMethod());
        self::assertEquals($notifications[0]->getOrderId(), $this->webhook->getMerchantReference());
        self::assertEquals('warning', $notifications[0]->getSeverity());
    }

    /**
     * @return void
     *
     * @throws AbortTaskExecutionException
     */
    public function testNotPushingNotification(): void
    {
        // arrange
        $this->webhook = new Webhook(
            Amount::fromInt(1111, Currency::getDefault()),
            EventCodes::CHARGEBACK,
            '2023-02-01T14:09:24+01:00',
            '123',
            'code',
            'reference',
            'pspReference',
            'paymentMethod',
            'reason',
            true,
            'originalRef',
            0,
            false
        );
        $task = new OrderUpdateTask($this->webhook);

        // act
        $task->execute();

        // assert
        $notifications = $this->shopNotificationService->getNotifications(1, 0);

        self::assertEmpty($notifications);
    }
}
