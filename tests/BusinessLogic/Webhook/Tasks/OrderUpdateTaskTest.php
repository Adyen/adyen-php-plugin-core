<?php

namespace Adyen\Core\Tests\BusinessLogic\Webhook\Tasks;

use Adyen\Core\BusinessLogic\Bootstrap\SingleInstance;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Currency;
use Adyen\Core\BusinessLogic\Domain\Integration\Order\OrderService;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Services\ShopNotificationService;
use Adyen\Core\BusinessLogic\Domain\Webhook\Models\Webhook;
use Adyen\Core\BusinessLogic\Webhook\Tasks\OrderUpdateTask;
use Adyen\Core\Infrastructure\ServiceRegister;
use Adyen\Core\Infrastructure\TaskExecution\Exceptions\AbortTaskExecutionException;
use Adyen\Core\Tests\BusinessLogic\Common\BaseSerializationTestCase;
use Adyen\Core\Tests\BusinessLogic\Common\MockComponents\MockOrderService;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;
use Adyen\Webhook\EventCodes;
use Exception;

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
            false,
            []
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
            false,
            []
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
            false,
            []
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
            false,
            []
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
            false,
            []
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
            false,
            []
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
            false,
            []
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
            false,
            []
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
            EventCodes::CHARGEBACK_REVERSED,
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
            false,
            []
        );
        $task = new OrderUpdateTask($this->webhook);

        // act
        $task->execute();

        // assert
        $notifications = $this->shopNotificationService->getNotifications(1, 0);

        self::assertEmpty($notifications);
    }

    /**
     * @return void
     *
     * @throws AbortTaskExecutionException
     */
    public function testPushingSuccessfulAuthorizationAdjustmentNotification(): void
    {
        // arrange
        $this->webhook = new Webhook(
            Amount::fromInt(1111, Currency::getDefault()),
            EventCodes::AUTHORISATION_ADJUSTMENT,
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
            false,
            []
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
    public function testPushingFailedAuthorizationAdjustmentNotification(): void
    {
        // arrange
        $this->webhook = new Webhook(
            Amount::fromInt(1111, Currency::getDefault()),
            EventCodes::AUTHORISATION_ADJUSTMENT,
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
            false,
            []
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

    public function testChargebackNotification(): void
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
            false,
            [
                "chargebackReasonCode" => "10.4",
	            "modificationMerchantReferences" => "",
	            "chargebackSchemeCode" => "visa",
	            "defensePeriodEndsAt" => "2021-11-29T01:30:57+01:00",
	            "autoDefended" => "false",
	            "defendable" => "true",
	            "disputeStatus" => "Undefended"
            ]
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
     * @throws Exception
     */
    public function testOrderCreatedFromWebhookWhenMissingForSupportedMethod(): void
    {
        // arrange
        $orderService = $this->registerMissingOrderService(true);
        $task = new OrderUpdateTask($this->authorisationWebhook('bcmc_mobile'));

        // act
        $task->execute();

        // assert
        self::assertSame(1, $orderService->createOrderCalls);
        self::assertNotEmpty($this->shopNotificationService->getNotifications(1, 0));
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testOrderNotCreatedFromWebhookForUnsupportedMethod(): void
    {
        // arrange
        $orderService = $this->registerMissingOrderService(true);
        $task = new OrderUpdateTask($this->authorisationWebhook('scheme'));

        // act
        $exception = null;
        try {
            $task->execute();
        } catch (Exception $e) {
            $exception = $e;
        }

        // assert
        self::assertNotNull($exception);
        self::assertSame(0, $orderService->createOrderCalls);
    }

    /**
     * Registers an order service that never finds the order, the way shop integrations report a missing order.
     *
     * @param bool $createResult
     *
     * @return MockOrderService
     */
    private function registerMissingOrderService(bool $createResult): MockOrderService
    {
        $orderService = new class($createResult) extends MockOrderService {
            /** @var int */
            public $createOrderCalls = 0;

            /** @var bool */
            private $createResult;

            public function __construct(bool $createResult)
            {
                $this->createResult = $createResult;
            }

            public function orderExists(string $merchantReference): bool
            {
                if ($this->createOrderCalls === 0) {
                    throw new Exception('Order ' . $merchantReference . ' not found.');
                }

                return true;
            }

            public function createOrderFromWebhook(Webhook $webhook): bool
            {
                $this->createOrderCalls++;

                return $this->createResult;
            }
        };

        TestServiceRegister::registerService(
            OrderService::class,
            new SingleInstance(static function () use ($orderService) {
                return $orderService;
            })
        );

        return $orderService;
    }

    /**
     * @param string $paymentMethod
     *
     * @return Webhook
     */
    private function authorisationWebhook(string $paymentMethod): Webhook
    {
        return new Webhook(
            Amount::fromInt(1111, Currency::getDefault()),
            EventCodes::AUTHORISATION,
            '2023-02-01T14:09:24+01:00',
            '123',
            'code',
            'reference',
            'pspReference',
            $paymentMethod,
            'reason',
            true,
            'originalRef',
            0,
            false,
            []
        );
    }
}
