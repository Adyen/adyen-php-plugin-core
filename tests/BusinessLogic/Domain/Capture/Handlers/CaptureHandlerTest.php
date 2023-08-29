<?php

namespace Adyen\Core\Tests\BusinessLogic\Domain\Capture\Handlers;

use Adyen\Core\BusinessLogic\Domain\Capture\Handlers\CaptureHandler;
use Adyen\Core\BusinessLogic\Domain\Capture\Proxies\CaptureProxy;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Currency;
use Adyen\Core\BusinessLogic\Domain\Connection\Repositories\ConnectionSettingsRepository;
use Adyen\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Models\CaptureType;
use Adyen\Core\BusinessLogic\Domain\Integration\Store\StoreService;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\ShopEvents;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Services\ShopNotificationService;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Exceptions\InvalidMerchantReferenceException;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models\HistoryItem;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Services\TransactionHistoryService;
use Adyen\Core\BusinessLogic\Domain\Webhook\Repositories\WebhookConfigRepository;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Adyen\Core\Tests\BusinessLogic\Domain\Capture\Mocks\MockCaptureProxy;
use Adyen\Core\Tests\BusinessLogic\Domain\Capture\Mocks\MockConnectionService;
use Adyen\Core\Tests\BusinessLogic\Domain\MockComponents\MockStoreService;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;
use Adyen\Webhook\EventCodes;
use Adyen\Webhook\PaymentStates;

/**
 * Class CaptureHandlerTest
 *
 * @package Adyen\Core\Tests\BusinessLogic\Domain\Capture\Handlers
 */
class CaptureHandlerTest extends BaseTestCase
{
    /**
     * @var TransactionHistoryService
     */
    private $transactionHistoryService;

    /**
     * @var ShopNotificationService
     */
    private $shopNotificationService;

    /**
     * @var MockCaptureProxy
     */
    private $proxy;

    /**
     * @var ConnectionService
     */
    private $connectionService;

    /**
     * @var CaptureHandler
     */
    private $captureHandler;


    /**
     * @return void
     *
     * @throws InvalidMerchantReferenceException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->proxy = new MockCaptureProxy();
        TestServiceRegister::registerService(
            CaptureProxy::class,
            function () {
                return $this->proxy;
            }
        );
        TestServiceRegister::registerService(
            StoreService::class,
            static function () {
                return new  MockStoreService();
            }
        );
        $this->connectionService = new MockConnectionService(
            TestServiceRegister::getService(ConnectionSettingsRepository::class),
            TestServiceRegister::getService(StoreService::class),
            TestServiceRegister::getService(WebhookConfigRepository::class)
        );

        $this->captureHandler = TestServiceRegister::getService(CaptureHandler::class);
        $this->shopNotificationService = TestServiceRegister::getService(ShopNotificationService::class);
        $this->transactionHistoryService = TestServiceRegister::getService(TransactionHistoryService::class);
        $this->transactionHistoryService->setTransactionHistory($this->mockTransactionHistory());
    }

    /**
     * @return void
     *
     * @throws InvalidMerchantReferenceException
     */
    public function testShopNotificationAdded(): void
    {
        // Arrange
        // Act
        $this->captureHandler->handle('reference', Amount::fromInt(1, Currency::getDefault()));

        // Assert
        $notification = $this->shopNotificationService->getNotifications(10, 0);
        self::assertNotEmpty($notification);
        self::assertCount(1, $notification);
    }

    /**
     * @return void
     *
     * @throws InvalidMerchantReferenceException
     */
    public function testHistoryItemAdded(): void
    {
        // Arrange
        // Act
        $this->captureHandler->handle('reference', Amount::fromInt(1, Currency::getDefault()));

        // Assert
        $history = $this->transactionHistoryService->getTransactionHistory('reference');
        self::assertCount(3, $history->collection()->getAll());
    }

    /**
     * @return void
     *
     * @throws InvalidMerchantReferenceException
     */
    public function testNotificationSuccess(): void
    {
        // Arrange
        // Act
        $this->captureHandler->handle('reference', Amount::fromInt(1, Currency::getDefault()));

        // Assert
        $notifications = $this->shopNotificationService->getNotifications(10, 0);
        $notification = $notifications[0];

        self::assertEquals('reference', $notification->getOrderId());
        self::assertEquals('mc', $notification->getPaymentMethod());
        self::assertEquals('info', $notification->getSeverity());
        self::assertEquals('Capture request has been sent to Adyen.', $notification->getMessage()->getMessage());
    }

    /**
     * @return void
     *
     * @throws InvalidMerchantReferenceException
     */
    public function testNotificationFail(): void
    {
        // Arrange
        $this->proxy->setMockSuccess(false);
        // Act
        $result = $this->captureHandler->handle('reference', Amount::fromInt(1, Currency::getDefault()));

        // Assert
        $notifications = $this->shopNotificationService->getNotifications(10, 0);
        $notification = $notifications[0];

        self::assertFalse($result);
        self::assertEquals('reference', $notification->getOrderId());
        self::assertEquals('mc', $notification->getPaymentMethod());
        self::assertEquals('error', $notification->getSeverity());
        self::assertEquals('Capture request failed.', $notification->getMessage()->getMessage());
    }

    /**
     * @return void
     *
     * @throws InvalidMerchantReferenceException
     */
    public function testHistoryItemSuccess(): void
    {
        // Arrange

        // Act
        $this->captureHandler->handle('reference', Amount::fromInt(1, Currency::getDefault()));

        // Assert
        $history = $this->transactionHistoryService->getTransactionHistory('reference');
        $item = $history->collection()->last();

        self::assertEquals('reference', $item->getMerchantReference());
        self::assertEquals('mc', $item->getPaymentMethod());
        self::assertEquals('capture1_psp1', $item->getPspReference());
        self::assertEquals(ShopEvents::CAPTURE_REQUEST, $item->getEventCode());
        self::assertEquals(false, $item->isLive());
        self::assertEquals(true, $item->getStatus());
        self::assertEquals(PaymentStates::STATE_PAID, $item->getPaymentState());
        self::assertEquals(0, $item->getRiskScore());
    }

    /**
     * @throws InvalidMerchantReferenceException
     */
    private function mockTransactionHistory(): TransactionHistory
    {
        $history = new TransactionHistory('reference',CaptureType::manual());
        $history->add(
            new HistoryItem(
                'psp1',
                'reference',
                EventCodes::AUTHORISATION,
                PaymentStates::STATE_PAID,
                'date1',
                true,
                Amount::fromInt(1, Currency::getDefault()),
                'mc',
                0,
                false
            )
        );
        $history->add(
            new HistoryItem(
                'psp2',
                'reference',
                EventCodes::CAPTURE,
                PaymentStates::STATE_PAID,
                'date1',
                true,
                Amount::fromInt(1, Currency::getDefault()),
                'mc',
                0,
                false
            )
        );

        return $history;
    }
}
