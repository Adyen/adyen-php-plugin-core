<?php

namespace Adyen\Core\Tests\BusinessLogic\Domain\AuthorizationAdjustment\Handlers;

use Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Exceptions\AdjustmentRequestAlreadySentException;
use Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Exceptions\AmountNotChangedException;
use Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Exceptions\InvalidAmountException;
use Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Exceptions\InvalidAuthorizationTypeException;
use Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Exceptions\InvalidPaymentStateException;
use Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Exceptions\OrderFullyCapturedException;
use Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Exceptions\PaymentLinkExistsException;
use Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Handlers\AuthorizationAdjustmentHandler;
use Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Proxies\AuthorizationAdjustmentProxy;
use Adyen\Core\BusinessLogic\Domain\Cancel\Proxies\CancelProxy;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Models\PaymentLink;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\CurrencyMismatchException;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Currency;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Models\CaptureType;
use Adyen\Core\BusinessLogic\Domain\Integration\Store\StoreService;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\AuthorizationType;
use Adyen\Core\BusinessLogic\Domain\Refund\Proxies\RefundProxy;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\ShopEvents;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Services\ShopNotificationService;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Exceptions\InvalidMerchantReferenceException;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models\HistoryItem;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Services\TransactionHistoryService;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Adyen\Core\Tests\BusinessLogic\Domain\AuthorizationAdjustment\Mocks\MockAdjustmentProxy;
use Adyen\Core\Tests\BusinessLogic\Domain\Cancel\Mocks\MockCancelProxy;
use Adyen\Core\Tests\BusinessLogic\Domain\Refund\Mocks\MockRefundProxy;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;
use Adyen\Core\Tests\BusinessLogic\Domain\MockComponents\MockStoreService;
use Adyen\Webhook\EventCodes;
use Adyen\Webhook\PaymentStates;
use Exception;

/**
 * Class AuthorizationAdjustmentHandlerTest.
 *
 * @package Adyen\Core\Tests\BusinessLogic\Domain\AuthorizationAdjustment\Handlers
 */
class AuthorizationAdjustmentHandlerTest extends BaseTestCase
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
     * @var MockAdjustmentProxy
     */
    private $proxy;

    /**
     * @var AuthorizationAdjustmentHandler
     */
    private $handler;

    /**
     * @return void
     *
     * @throws InvalidMerchantReferenceException
     */
    protected function setUp(): void
    {
        parent::setUp();

        TestServiceRegister::registerService(
            RefundProxy::class,
            function () {
                return new MockRefundProxy();
            }
        );

        $this->proxy = new MockAdjustmentProxy();
        TestServiceRegister::registerService(
            AuthorizationAdjustmentProxy::class,
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

        TestServiceRegister::registerService(
            CancelProxy::class,
            function () {
                return new MockCancelProxy();
            }
        );

        $this->handler = TestServiceRegister::getService(AuthorizationAdjustmentHandler::class);
        $this->shopNotificationService = TestServiceRegister::getService(ShopNotificationService::class);
        $this->transactionHistoryService = TestServiceRegister::getService(TransactionHistoryService::class);
        $this->transactionHistoryService->setTransactionHistory($this->mockTransactionHistory());
    }

    /**
     * @return void
     *
     * @throws InvalidMerchantReferenceException
     * @throws InvalidAuthorizationTypeException
     * @throws InvalidPaymentStateException
     * @throws OrderFullyCapturedException
     * @throws PaymentLinkExistsException
     * @throws CurrencyMismatchException
     * @throws Exception
     */
    public function testShopNotificationAdded(): void
    {
        // Arrange
        // Act
        $this->handler->handleExtendingAuthorizationPeriod('reference');

        // Assert
        $notification = $this->shopNotificationService->getNotifications(10, 0);
        self::assertNotEmpty($notification);
        self::assertCount(1, $notification);
    }

    /**
     * @return void
     *
     * @throws CurrencyMismatchException
     * @throws InvalidAuthorizationTypeException
     * @throws InvalidMerchantReferenceException
     * @throws InvalidPaymentStateException
     * @throws OrderFullyCapturedException
     * @throws PaymentLinkExistsException
     */
    public function testHistoryItemAdded(): void
    {
        // Arrange
        // Act
        $this->handler->handleExtendingAuthorizationPeriod('reference');
        // Assert
        $history = $this->transactionHistoryService->getTransactionHistory('reference');
        self::assertCount(3, $history->collection()->getAll());
    }

    /**
     * @return void
     *
     * @throws CurrencyMismatchException
     * @throws InvalidAuthorizationTypeException
     * @throws InvalidMerchantReferenceException
     * @throws InvalidPaymentStateException
     * @throws OrderFullyCapturedException
     * @throws PaymentLinkExistsException
     * @throws Exception
     */
    public function testNotificationSuccess(): void
    {
        // Arrange
        // Act
        $this->handler->handleExtendingAuthorizationPeriod('reference');

        // Assert
        $notifications = $this->shopNotificationService->getNotifications(10, 0);
        $notification = $notifications[0];

        self::assertEquals('reference', $notification->getOrderId());
        self::assertEquals('mc', $notification->getPaymentMethod());
        self::assertEquals('info', $notification->getSeverity());
        self::assertEquals('Authorization adjustment request has been sent successfully to Adyen.',
            $notification->getMessage()->getMessage());
    }

    /**
     * @return void
     *
     * @throws CurrencyMismatchException
     * @throws InvalidAuthorizationTypeException
     * @throws InvalidMerchantReferenceException
     * @throws InvalidPaymentStateException
     * @throws OrderFullyCapturedException
     * @throws PaymentLinkExistsException
     */
    public function testNotificationFail(): void
    {
        // Arrange
        $this->proxy->setMockSuccess(false);
        // Act
        $this->handler->handleExtendingAuthorizationPeriod('reference');

        // Assert
        $notifications = $this->shopNotificationService->getNotifications(10, 0);
        $notification = $notifications[0];

        self::assertEquals('reference', $notification->getOrderId());
        self::assertEquals('mc', $notification->getPaymentMethod());
        self::assertEquals('error', $notification->getSeverity());
        self::assertEquals('Authorization adjustment request failed.', $notification->getMessage()->getMessage());
    }

    /**
     * @return void
     *
     * @throws CurrencyMismatchException
     * @throws InvalidAuthorizationTypeException
     * @throws InvalidMerchantReferenceException
     * @throws InvalidPaymentStateException
     * @throws OrderFullyCapturedException
     * @throws PaymentLinkExistsException
     */
    public function testHistoryItemSuccess(): void
    {
        // Arrange

        // Act
        $this->handler->handleExtendingAuthorizationPeriod('reference');

        // Assert
        $history = $this->transactionHistoryService->getTransactionHistory('reference');
        $item = $history->collection()->last();

        self::assertEquals('reference', $item->getMerchantReference());
        self::assertEquals('mc', $item->getPaymentMethod());
        self::assertEquals('authorization_adjustment1_psp1', $item->getPspReference());
        self::assertEquals(ShopEvents::AUTHORIZATION_ADJUSTMENT_REQUEST, $item->getEventCode());
        self::assertFalse($item->isLive());
        self::assertTrue($item->getStatus());
        self::assertEquals(PaymentStates::STATE_PAID, $item->getPaymentState());
        self::assertEquals(0, $item->getRiskScore());
    }

    /**
     * @return void
     *
     * @throws CurrencyMismatchException
     * @throws InvalidAuthorizationTypeException
     * @throws InvalidMerchantReferenceException
     * @throws InvalidPaymentStateException
     * @throws OrderFullyCapturedException
     * @throws PaymentLinkExistsException
     */
    public function testHistoryItemFail(): void
    {
        // Arrange
        $this->proxy->setMockSuccess(false);
        // Act
        $this->handler->handleExtendingAuthorizationPeriod('reference');

        // Assert
        $history = $this->transactionHistoryService->getTransactionHistory('reference');
        $item = $history->collection()->last();

        self::assertEquals('reference', $item->getMerchantReference());
        self::assertEquals('mc', $item->getPaymentMethod());
        self::assertEquals('authorization_adjustment1_psp1', $item->getPspReference());
        self::assertEquals(ShopEvents::AUTHORIZATION_ADJUSTMENT_REQUEST, $item->getEventCode());
        self::assertFalse($item->isLive());
        self::assertFalse($item->getStatus());
        self::assertEquals(PaymentStates::STATE_PAID, $item->getPaymentState());
        self::assertEquals(0, $item->getRiskScore());
    }

    /**
     * @return void
     *
     * @throws CurrencyMismatchException
     * @throws InvalidAuthorizationTypeException
     * @throws InvalidMerchantReferenceException
     * @throws InvalidPaymentStateException
     * @throws OrderFullyCapturedException
     * @throws PaymentLinkExistsException
     */
    public function testInvalidAuthorizationTypeWhenFinalAuthorization(): void
    {
        // Arrange
        $this->transactionHistoryService->setTransactionHistory(
            new TransactionHistory('reference',
                CaptureType::manual(),
                0,
                null,
                AuthorizationType::finalAuthorization())
        );
        $this->expectException(InvalidAuthorizationTypeException::class);
        // Act
        $this->handler->handleExtendingAuthorizationPeriod('reference');
        // Assert
    }

    /**
     * @return void
     *
     * @throws CurrencyMismatchException
     * @throws InvalidAuthorizationTypeException
     * @throws InvalidMerchantReferenceException
     * @throws InvalidPaymentStateException
     * @throws OrderFullyCapturedException
     * @throws PaymentLinkExistsException
     */
    public function testInvalidAuthorizationTypeWhenNoAuthorizationTypeIsSet(): void
    {
        // Arrange
        $this->transactionHistoryService->setTransactionHistory(
            new TransactionHistory(
                'reference',
                CaptureType::manual())
        );
        $this->expectException(InvalidAuthorizationTypeException::class);
        // Act
        $this->handler->handleExtendingAuthorizationPeriod('reference');
        // Assert
    }

    /**
     * @return void
     *
     * @throws CurrencyMismatchException
     * @throws InvalidAuthorizationTypeException
     * @throws InvalidMerchantReferenceException
     * @throws InvalidPaymentStateException
     * @throws OrderFullyCapturedException
     * @throws PaymentLinkExistsException
     */
    public function testOrderFullyCapturedValidation(): void
    {
        // Arrange
        $this->transactionHistoryService->setTransactionHistory(
            new TransactionHistory('reference',
                CaptureType::manual(),
                0,
                null,
                AuthorizationType::preAuthorization(),
                [
                    new HistoryItem(
                        'psp1',
                        'reference',
                        EventCodes::AUTHORISATION,
                        PaymentStates::STATE_PAID,
                        'date1',
                        true,
                        Amount::fromInt(2, Currency::getDefault()),
                        'mc',
                        0,
                        false
                    ),
                    new HistoryItem(
                        'psp2',
                        'reference',
                        EventCodes::CAPTURE,
                        PaymentStates::STATE_PAID,
                        'date2',
                        true,
                        Amount::fromInt(2, Currency::getDefault()),
                        'mc',
                        0,
                        false
                    )
                ]
            )
        );
        $this->expectException(OrderFullyCapturedException::class);
        // Act
        $this->handler->handleExtendingAuthorizationPeriod('reference');
        // Assert
    }

    /**
     * @return void
     *
     * @throws CurrencyMismatchException
     * @throws InvalidAuthorizationTypeException
     * @throws InvalidMerchantReferenceException
     * @throws InvalidPaymentStateException
     * @throws OrderFullyCapturedException
     * @throws PaymentLinkExistsException
     */
    public function testPaymentLinkExists(): void
    {
        // Arrange
        $transactionHistory = $this->mockTransactionHistory();
        $transactionHistory->setPaymentLink(new PaymentLink('url', '9999-11-10T13:31:17+01:00'));
        $this->transactionHistoryService->setTransactionHistory($transactionHistory);
        $this->expectException(PaymentLinkExistsException::class);
        // Act
        $this->handler->handleExtendingAuthorizationPeriod('reference');
        // Assert
    }

    /**
     * @return void
     *
     * @throws CurrencyMismatchException
     * @throws InvalidAuthorizationTypeException
     * @throws InvalidMerchantReferenceException
     * @throws InvalidPaymentStateException
     * @throws OrderFullyCapturedException
     * @throws PaymentLinkExistsException
     */
    public function testInvalidPaymentStateValidation(): void
    {
        // Arrange
        $transactionHistory = $this->mockTransactionHistory();
        $transactionHistory->add(new HistoryItem(
            'psp3',
            'reference',
            EventCodes::CANCELLATION,
            PaymentStates::STATE_CANCELLED,
            'date3',
            true,
            Amount::fromInt(2, Currency::getDefault()),
            'mc',
            0,
            false
        ));
        $this->transactionHistoryService->setTransactionHistory($transactionHistory);
        $this->expectException(InvalidPaymentStateException::class);
        // Act
        $this->handler->handleExtendingAuthorizationPeriod('reference');
        // Assert
    }

    /**
     * @return void
     *
     * @throws InvalidMerchantReferenceException
     * @throws InvalidAuthorizationTypeException
     * @throws InvalidPaymentStateException
     * @throws OrderFullyCapturedException
     * @throws PaymentLinkExistsException
     * @throws CurrencyMismatchException
     * @throws Exception
     */
    public function testModificationShopNotificationAdded(): void
    {
        // Arrange
        // Act
        $this->handler->handleOrderModifications('reference', Amount::fromFloat(1.1, Currency::getDefault()));

        // Assert
        $notification = $this->shopNotificationService->getNotifications(10, 0);
        self::assertNotEmpty($notification);
        self::assertCount(1, $notification);
    }

    /**
     * @return void
     *
     * @throws AdjustmentRequestAlreadySentException
     * @throws AmountNotChangedException
     * @throws CurrencyMismatchException
     * @throws InvalidAmountException
     * @throws InvalidAuthorizationTypeException
     * @throws InvalidMerchantReferenceException
     * @throws InvalidPaymentStateException
     * @throws OrderFullyCapturedException
     * @throws PaymentLinkExistsException
     */
    public function testModificationHistoryItemAdded(): void
    {
        // Arrange
        // Act
        $this->handler->handleOrderModifications('reference', Amount::fromFloat(1.1, Currency::getDefault()));
        // Assert
        $history = $this->transactionHistoryService->getTransactionHistory('reference');
        self::assertCount(3, $history->collection()->getAll());
    }

    /**
     * @return void
     *
     * @throws CurrencyMismatchException
     * @throws InvalidAuthorizationTypeException
     * @throws InvalidMerchantReferenceException
     * @throws InvalidPaymentStateException
     * @throws OrderFullyCapturedException
     * @throws PaymentLinkExistsException
     * @throws Exception
     */
    public function testModificationNotificationSuccess(): void
    {
        // Arrange
        // Act
        $this->handler->handleOrderModifications('reference', Amount::fromFloat(1.1, Currency::getDefault()));

        // Assert
        $notifications = $this->shopNotificationService->getNotifications(10, 0);
        $notification = $notifications[0];

        self::assertEquals('reference', $notification->getOrderId());
        self::assertEquals('mc', $notification->getPaymentMethod());
        self::assertEquals('info', $notification->getSeverity());
        self::assertEquals('Authorization adjustment request has been sent successfully to Adyen.',
            $notification->getMessage()->getMessage());
    }

    /**
     * @return void
     *
     * @throws AdjustmentRequestAlreadySentException
     * @throws AmountNotChangedException
     * @throws CurrencyMismatchException
     * @throws InvalidAmountException
     * @throws InvalidAuthorizationTypeException
     * @throws InvalidMerchantReferenceException
     * @throws InvalidPaymentStateException
     * @throws OrderFullyCapturedException
     * @throws PaymentLinkExistsException
     */
    public function testModificationNotificationFail(): void
    {
        // Arrange
        $this->proxy->setMockSuccess(false);
        // Act
        $this->handler->handleOrderModifications('reference', Amount::fromFloat(1.1, Currency::getDefault()));

        // Assert
        $notifications = $this->shopNotificationService->getNotifications(10, 0);
        $notification = $notifications[0];

        self::assertEquals('reference', $notification->getOrderId());
        self::assertEquals('mc', $notification->getPaymentMethod());
        self::assertEquals('error', $notification->getSeverity());
        self::assertEquals('Authorization adjustment request failed.', $notification->getMessage()->getMessage());
    }

    /**
     * @return void
     *
     * @throws AdjustmentRequestAlreadySentException
     * @throws AmountNotChangedException
     * @throws CurrencyMismatchException
     * @throws InvalidAmountException
     * @throws InvalidAuthorizationTypeException
     * @throws InvalidMerchantReferenceException
     * @throws InvalidPaymentStateException
     * @throws OrderFullyCapturedException
     * @throws PaymentLinkExistsException
     */
    public function testModificationHistoryItemSuccess(): void
    {
        // Arrange
        // Act
        $this->handler->handleOrderModifications('reference', Amount::fromFloat(1.1, Currency::getDefault()));

        // Assert
        $history = $this->transactionHistoryService->getTransactionHistory('reference');
        $item = $history->collection()->last();

        self::assertEquals('reference', $item->getMerchantReference());
        self::assertEquals('mc', $item->getPaymentMethod());
        self::assertEquals('authorization_adjustment1_psp1', $item->getPspReference());
        self::assertEquals(ShopEvents::AUTHORIZATION_ADJUSTMENT_REQUEST, $item->getEventCode());
        self::assertFalse($item->isLive());
        self::assertTrue($item->getStatus());
        self::assertEquals(PaymentStates::STATE_PAID, $item->getPaymentState());
        self::assertEquals(0, $item->getRiskScore());
    }

    /**
     * @return void
     *
     * @throws AdjustmentRequestAlreadySentException
     * @throws AmountNotChangedException
     * @throws CurrencyMismatchException
     * @throws InvalidAmountException
     * @throws InvalidAuthorizationTypeException
     * @throws InvalidMerchantReferenceException
     * @throws InvalidPaymentStateException
     * @throws OrderFullyCapturedException
     * @throws PaymentLinkExistsException
     */
    public function testModificationHistoryItemFail(): void
    {
        // Arrange
        $this->proxy->setMockSuccess(false);
        // Act
        $this->handler->handleOrderModifications('reference', Amount::fromFloat(1.1, Currency::getDefault()));

        // Assert
        $history = $this->transactionHistoryService->getTransactionHistory('reference');
        $item = $history->collection()->last();

        self::assertEquals('reference', $item->getMerchantReference());
        self::assertEquals('mc', $item->getPaymentMethod());
        self::assertEquals('authorization_adjustment1_psp1', $item->getPspReference());
        self::assertEquals(ShopEvents::AUTHORIZATION_ADJUSTMENT_REQUEST, $item->getEventCode());
        self::assertFalse($item->isLive());
        self::assertFalse($item->getStatus());
        self::assertEquals(PaymentStates::STATE_PAID, $item->getPaymentState());
        self::assertEquals(0, $item->getRiskScore());
    }

    /**
     * @return void
     *
     * @throws AdjustmentRequestAlreadySentException
     * @throws AmountNotChangedException
     * @throws CurrencyMismatchException
     * @throws InvalidAmountException
     * @throws InvalidAuthorizationTypeException
     * @throws InvalidMerchantReferenceException
     * @throws InvalidPaymentStateException
     * @throws OrderFullyCapturedException
     * @throws PaymentLinkExistsException
     */
    public function testModificationInvalidAuthorizationTypeWhenFinalAuthorization(): void
    {
        // Arrange
        $this->transactionHistoryService->setTransactionHistory(
            new TransactionHistory('reference',
                CaptureType::manual(),
                0,
                null,
                AuthorizationType::finalAuthorization())
        );
        $this->expectException(InvalidAuthorizationTypeException::class);
        // Act
        $this->handler->handleOrderModifications('reference', Amount::fromFloat(1.1, Currency::getDefault()));
        // Assert
    }

    /**
     * @return void
     *
     * @throws InvalidMerchantReferenceException
     * @throws InvalidAuthorizationTypeException
     * @throws InvalidPaymentStateException
     * @throws OrderFullyCapturedException
     * @throws PaymentLinkExistsException
     * @throws CurrencyMismatchException
     * @throws Exception
     */
    public function testModificationCancelWhenAmountIsZero(): void
    {
        // Arrange
        $history = new TransactionHistory('reference',
            CaptureType::manual(),
            0,
            null,
            AuthorizationType::preAuthorization(),
        [
            new HistoryItem(
                'psp1',
                'reference',
                EventCodes::AUTHORISATION,
                PaymentStates::STATE_PAID,
                'date1',
                true,
                Amount::fromInt(2, Currency::getDefault()),
                'mc',
                0,
                false
            )
        ]);
        $this->transactionHistoryService->setTransactionHistory($history);
        // Act
        $this->handler->handleOrderModifications('reference', Amount::fromFloat(0, Currency::getDefault()));

        // Assert
        $notification = $this->shopNotificationService->getNotifications(10, 0);
        $history = $this->transactionHistoryService->getTransactionHistory('reference');
        $item = $history->collection()->last();

        self::assertEquals('reference', $item->getMerchantReference());
        self::assertEquals('mc', $item->getPaymentMethod());
        self::assertEquals(ShopEvents::CANCELLATION_REQUEST, $item->getEventCode());
        self::assertNotEmpty($notification);
        self::assertCount(1, $notification);
    }

    /**
     * @return void
     *
     * @throws InvalidMerchantReferenceException
     * @throws InvalidAuthorizationTypeException
     * @throws InvalidPaymentStateException
     * @throws OrderFullyCapturedException
     * @throws PaymentLinkExistsException
     * @throws CurrencyMismatchException
     * @throws Exception
     */
    public function testModificationInvalidAmount(): void
    {
        // Arrange
        $this->expectException(InvalidAmountException::class);
        // Act
        $this->handler->handleOrderModifications('reference', Amount::fromFloat(0, Currency::getDefault()));
        // Assert
    }

    /**
     * @return void
     *
     * @throws InvalidMerchantReferenceException
     * @throws InvalidAuthorizationTypeException
     * @throws InvalidPaymentStateException
     * @throws OrderFullyCapturedException
     * @throws PaymentLinkExistsException
     * @throws CurrencyMismatchException
     * @throws Exception
     */
    public function testModificationAmountNotChangedException(): void
    {
        // Arrange
        $history = new TransactionHistory('reference',
            CaptureType::manual(),
            0,
            null,
            AuthorizationType::preAuthorization(),
            [
                new HistoryItem(
                    'psp1',
                    'reference',
                    EventCodes::AUTHORISATION,
                    PaymentStates::STATE_PAID,
                    'date1',
                    true,
                    Amount::fromInt(2, Currency::getDefault()),
                    'mc',
                    0,
                    false
                )
            ]);
        $this->transactionHistoryService->setTransactionHistory($history);
        $this->expectException(AmountNotChangedException::class);
        // Act
        $this->handler->handleOrderModifications('reference', Amount::fromInt(2, Currency::getDefault()));
        // Assert
    }

    /**
     * @return void
     *
     * @throws InvalidMerchantReferenceException
     * @throws InvalidAuthorizationTypeException
     * @throws InvalidPaymentStateException
     * @throws OrderFullyCapturedException
     * @throws PaymentLinkExistsException
     * @throws CurrencyMismatchException
     * @throws Exception
     */
    public function testModificationRequestAlreadySentException(): void
    {
        // Arrange
        $history = new TransactionHistory('reference',
            CaptureType::manual(),
            0,
            null,
            AuthorizationType::preAuthorization(),
            [
                new HistoryItem(
                    'psp1',
                    'reference',
                    EventCodes::AUTHORISATION,
                    PaymentStates::STATE_PAID,
                    'date1',
                    true,
                    Amount::fromInt(2, Currency::getDefault()),
                    'mc',
                    0,
                    false
                ),
                new HistoryItem(
                    'psp2',
                    'reference',
                    ShopEvents::AUTHORIZATION_ADJUSTMENT_REQUEST,
                    PaymentStates::STATE_PAID,
                    'date1',
                    true,
                    Amount::fromInt(3, Currency::getDefault()),
                    'mc',
                    0,
                    false
                )
            ]);
        $this->transactionHistoryService->setTransactionHistory($history);
        $this->expectException(AdjustmentRequestAlreadySentException::class);
        // Act
        $this->handler->handleOrderModifications('reference', Amount::fromInt(3, Currency::getDefault()));
        // Assert
    }

    /**
     * @throws InvalidMerchantReferenceException
     */
    private function mockTransactionHistory(): TransactionHistory
    {
        $history = new TransactionHistory('reference',
            CaptureType::manual(),
            0,
            null,
            AuthorizationType::preAuthorization());
        $history->add(
            new HistoryItem(
                'psp1',
                'reference',
                EventCodes::AUTHORISATION,
                PaymentStates::STATE_PAID,
                'date1',
                true,
                Amount::fromInt(2, Currency::getDefault()),
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
