<?php

namespace Adyen\Core\Tests\BusinessLogic\Domain\Webhook\Services;

use Adyen\Core\BusinessLogic\Bootstrap\SingleInstance;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Currency;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PaymentMethodCode;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Models\CaptureType;
use Adyen\Core\BusinessLogic\Domain\Integration\Order\OrderService;
use Adyen\Core\BusinessLogic\Domain\Integration\Store\StoreService;
use Adyen\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\AuthorizationType;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Exceptions\InvalidMerchantReferenceException;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models\HistoryItem;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Repositories\TransactionHistoryRepository;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Services\TransactionHistoryService;
use Adyen\Core\BusinessLogic\Domain\Webhook\Models\Webhook;
use Adyen\Core\BusinessLogic\Domain\Webhook\Services\WebhookSynchronizationService;
use Adyen\Core\BusinessLogic\Webhook\Services\OrderStatusMappingService;
use Adyen\Core\Infrastructure\Utility\TimeProvider;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Adyen\Core\Tests\BusinessLogic\Common\MockComponents\MockStoreService;
use Adyen\Core\Tests\BusinessLogic\Domain\TransactionHistory\MockComponents\MockTransactionRepository;
use Adyen\Core\Tests\BusinessLogic\Domain\Webhook\Mocks\MockOrderService;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;
use Adyen\Webhook\EventCodes;
use Adyen\Webhook\PaymentStates;
use Exception;

/**
 * Class WebhookSynchronizationServiceTest
 *
 * @package Adyen\Core\Tests\BusinessLogic\Webhook
 */
class WebhookSynchronizationServiceTest extends BaseTestCase
{
    /**
     * @var OrderService
     */
    private $orderService;

    /**
     * @var WebhookSynchronizationService
     */
    private $service;

    /**
     * @var OrderStatusMappingService
     */
    private $orderStatusMappingService;

    /**
     * @var Webhook
     */
    private $webhook;

    /**
     * @var StoreService
     */
    private $storeService;

    /**
     * @var TransactionHistoryRepository
     */
    private $transactionHistoryRepository;

    /**
     * @var TransactionHistoryService
     */
    private $transactionService;

    /**
     * @var TimeProvider
     */
    private $timeProvider;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->orderService = new MockOrderService();
        $this->storeService = new MockStoreService();
        $this->transactionHistoryRepository = new MockTransactionRepository();


        TestServiceRegister::registerService(
            StoreService::class,
            new SingleInstance(function () {
                return $this->storeService;
            })
        );

        TestServiceRegister::registerService(
            TransactionHistoryRepository::class,
            new SingleInstance(function () {
                return $this->transactionHistoryRepository;
            })
        );

        TestServiceRegister::registerService(
            OrderService::class,
            new SingleInstance(function () {
                return $this->orderService;
            })
        );

        $this->transactionService = TestServiceRegister::getService(TransactionHistoryService::class);
        $this->orderStatusMappingService = TestServiceRegister::getService(OrderStatusMappingService::class);
        $this->service = TestServiceRegister::getService(WebhookSynchronizationService::class);
        $this->timeProvider = TestServiceRegister::getService(TimeProvider::class);
        $this->webhook = new Webhook(
            Amount::fromInt(1, Currency::getDefault()),
            'code',
            'date',
            'hmac',
            'mc',
            'mr',
            'psp',
            'method',
            'r',
            true,
            'originalRef',
            0,
            false,
            []
        );
        $this->storeService->setMockDefaultMap([
            PaymentStates::STATE_IN_PROGRESS => '12',
            PaymentStates::STATE_PENDING => '13',
            PaymentStates::STATE_PAID => '14',
            PaymentStates::STATE_FAILED => '11',
            PaymentStates::STATE_REFUNDED => '41',
            PaymentStates::STATE_CANCELLED => '42',
            PaymentStates::STATE_PARTIALLY_REFUNDED => '167',
            PaymentStates::STATE_NEW => '12',
            PaymentStates::CHARGE_BACK => '86'
        ]);
    }

    /**
     * @throws Exception
     */
    public function testSyncNeededNoTransactionHistory(): void
    {
        // act
        $result = StoreContext::doWithStore('1', [$this->service, 'isSynchronizationNeeded'], [$this->webhook]);

        // assert
        self::assertFalse($result);
    }

    /**
     * @throws Exception
     */
    public function testSyncNotNeededHasDuplicates(): void
    {
        // arrange
        $this->transactionHistoryRepository->setTransactionHistory($this->transactionHistory());
        $this->webhook = new Webhook(
            Amount::fromInt(1, Currency::getDefault()),
            'CODE1',
            'data',
            '',
            '',
            '',
            'pspRef1',
            '',
            '',
            true,
            'originalPsp',
            0,
            false,
            []
        );
        // act
        $result = StoreContext::doWithStore('1', [$this->service, 'isSynchronizationNeeded'], [$this->webhook]);
        // assert
        self::assertFalse($result);
    }

    /**
     * @throws Exception
     */
    public function testSyncNotNeededIsTestWebhook(): void
    {
        // arrange
        $this->webhook = new Webhook(
            Amount::fromInt(1, Currency::getDefault()),
            'CODE1',
            'data',
            '',
            '',
            'testWebhook',
            'pspRef1',
            '',
            '',
            true,
            'originalPsp',
            0,
            false,
            []
        );
        // act
        $result = StoreContext::doWithStore('1', [$this->service, 'isSynchronizationNeeded'], [$this->webhook]);
        // assert
        self::assertFalse($result);
    }

    /**
     * @throws Exception
     */
    public function testSyncNeededNoDuplicates(): void
    {
        // arrange
        $this->transactionHistoryRepository->setTransactionHistory($this->transactionHistory());
        $this->webhook = new Webhook(
            Amount::fromInt(1, Currency::getDefault()),
            'CODE16',
            'data',
            '',
            '',
            '',
            'pspRef16',
            '',
            '',
            true,
            'originalPsp',
            0,
            false,
            []
        );
        // act
        $result = StoreContext::doWithStore('1', [$this->service, 'isSynchronizationNeeded'], [$this->webhook]);
        // assert
        self::assertTrue($result);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testSynchronizeChangesNoTransactionHistoryWebhookSuccess(): void
    {
        // arrange
        $this->webhook = new Webhook(
            Amount::fromInt(1, Currency::getDefault()),
            EventCodes::AUTHORISATION,
            '2023-02-01T14:09:24+01:00',
            'coqCmt/IZ4E3CzPvMY8zTjQVL5hYJUiBRg8UU+iCWo0=',
            'TestMerchant',
            'merchantRef',
            '7914073381342284',
            (string)PaymentMethodCode::giftCard(),
            'reason',
            true,
            '',
            0,
            false,
            []
        );

        // act
        StoreContext::doWithStore('1', [$this->service, 'synchronizeChanges'], [$this->webhook]);

        // assert
        $transaction = $this->transactionService->getTransactionHistory($this->webhook->getMerchantReference());
        $expected = $this->expectedTransaction();
        self::assertEquals($expected, $transaction);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testSynchronizeChangesNoHistoryItemWebhookSuccess(): void
    {
        // arrange
        $this->webhook = new Webhook(
            Amount::fromInt(1, Currency::getDefault()),
            EventCodes::AUTHORISATION,
            '2023-02-01T14:09:24+01:00',
            'coqCmt/IZ4E3CzPvMY8zTjQVL5hYJUiBRg8UU+iCWo0=',
            'TestMerchant',
            'merchantRef',
            '79140733813422890',
            (string)PaymentMethodCode::giftCard(),
            'reason',
            true,
            'ref',
            0,
            false,
            []
        );
        $this->transactionService->setTransactionHistory($this->expectedTransaction());

        // act
        StoreContext::doWithStore('1', [$this->service, 'synchronizeChanges'], [$this->webhook]);

        // assert
        $transactionHistory = $this->transactionService->getTransactionHistory($this->webhook->getMerchantReference());

        self::assertCount(2, $transactionHistory->collection()->getAll());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testSynchronizeChangesWithTransactionHistoryWebhookFail(): void
    {
        // arrange
        $this->webhook = new Webhook(
            Amount::fromInt(1, Currency::getDefault()),
            EventCodes::CAPTURE,
            '2023-02-01T14:09:24+01:00',
            'coqCmt/IZ4E3CzPvMY8zTjQVL5hYJUiBRg8UU+iCWo0=',
            'TestMerchant',
            'merchantRef',
            '79140733813422890',
            (string)PaymentMethodCode::giftCard(),
            'reason',
            false,
            '7914073381342284',
            0,
            false,
            []
        );
        $transactionHistory = $this->expectedTransaction();
        $this->transactionHistoryRepository->setTransactionHistory($transactionHistory);
        $this->orderService->setMockOrderExists(true);

        // act
        StoreContext::doWithStore('1', [$this->service, 'synchronizeChanges'], [$this->webhook]);

        // assert
        $transactionHistory = $this->transactionService->getTransactionHistory($this->webhook->getMerchantReference());

        self::assertEquals(PaymentStates::STATE_PAID, $transactionHistory->collection()->last()->getPaymentState());
        self::assertCount(2, $transactionHistory->collection()->getAll());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testSynchronizeChangesWithSuccessfulOrderClosedWebhook(): void
    {
        // arrange
        $this->webhook = new Webhook(
            Amount::fromInt(1, Currency::getDefault()),
            EventCodes::ORDER_CLOSED,
            '2023-02-01T14:09:24+01:00',
            'coqCmt/IZ4E3CzPvMY8zTjQVL5hYJUiBRg8UU+iCWo0=',
            'TestMerchant',
            'merchantRef',
            '79140733813420890',
            (string)PaymentMethodCode::giftCard(),
            'reason',
            true,
            '',
            0,
            false,
            []
        );
        $transactionHistory = new TransactionHistory(
            'merchantRef', CaptureType::manual(), 1, Currency::getDefault(), null,
            [
                new HistoryItem(
                    '7914073381342284',
                    'merchantRef',
                    EventCodes::ORDER_OPENED,
                    PaymentStates::STATE_NEW,
                    '2023-02-01T14:09:24+01:00',
                    true,
                    Amount::fromInt(1, Currency::getDefault()),
                    (string)PaymentMethodCode::giftCard(),
                    0,
                    false,
                    '',
                    CaptureType::immediate(),
                    1,
                    $this->timeProvider->getCurrentLocalTime()->getTimestamp()
                ),
                new HistoryItem(
                    '7914073381342287',
                    'merchantRef',
                    EventCodes::OFFER_CLOSED,
                    PaymentStates::STATE_CANCELLED,
                    '2023-02-01T14:09:24+01:00',
                    true,
                    Amount::fromInt(1, Currency::getDefault()),
                    (string)PaymentMethodCode::giftCard(),
                    0,
                    false,
                    '',
                    CaptureType::immediate(),
                    1,
                    $this->timeProvider->getCurrentLocalTime()->getTimestamp()
                ),
            ],
            '',
            '',
            []
        );
        $this->transactionHistoryRepository->setTransactionHistory($transactionHistory);
        $this->orderService->setMockOrderExists(true);

        // act
        StoreContext::doWithStore('1', [$this->service, 'synchronizeChanges'], [$this->webhook]);

        // assert
        $transactionHistory = $this->transactionService->getTransactionHistory($this->webhook->getMerchantReference());

        self::assertEquals(PaymentStates::STATE_PAID, $transactionHistory->collection()->last()->getPaymentState());
        self::assertCount(3, $transactionHistory->collection()->getAll());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testSynchronizeChangesWithFailOrderClosedWebhook(): void
    {
        // arrange
        $this->webhook = new Webhook(
            Amount::fromInt(1, Currency::getDefault()),
            EventCodes::ORDER_CLOSED,
            '2023-02-01T14:09:24+01:00',
            'coqCmt/IZ4E3CzPvMY8zTjQVL5hYJUiBRg8UU+iCWo0=',
            'TestMerchant',
            'merchantRef',
            '79140733813420890',
            (string)PaymentMethodCode::giftCard(),
            'reason',
            false,
            '',
            0,
            false,
            []
        );
        $transactionHistory = new TransactionHistory(
            'merchantRef', CaptureType::manual(), 1, Currency::getDefault(), null,
            [
                new HistoryItem(
                    '7914073381342284',
                    'merchantRef',
                    EventCodes::ORDER_OPENED,
                    PaymentStates::STATE_NEW,
                    '2023-02-01T14:09:24+01:00',
                    true,
                    Amount::fromInt(1, Currency::getDefault()),
                    (string)PaymentMethodCode::giftCard(),
                    0,
                    false,
                    '',
                    CaptureType::immediate(),
                    1,
                    $this->timeProvider->getCurrentLocalTime()->getTimestamp()
                ),
                new HistoryItem(
                    '7914073381342287',
                    'merchantRef',
                    EventCodes::OFFER_CLOSED,
                    PaymentStates::STATE_CANCELLED,
                    '2023-02-01T14:09:24+01:00',
                    true,
                    Amount::fromInt(1, Currency::getDefault()),
                    (string)PaymentMethodCode::giftCard(),
                    0,
                    false,
                    '',
                    CaptureType::immediate(),
                    1,
                    $this->timeProvider->getCurrentLocalTime()->getTimestamp()
                ),
            ],
            '',
            '',
            []
        );
        $this->transactionHistoryRepository->setTransactionHistory($transactionHistory);
        $this->orderService->setMockOrderExists(true);

        // act
        StoreContext::doWithStore('1', [$this->service, 'synchronizeChanges'], [$this->webhook]);

        // assert
        $transactionHistory = $this->transactionService->getTransactionHistory($this->webhook->getMerchantReference());

        self::assertEquals(PaymentStates::STATE_CANCELLED, $transactionHistory->collection()->last()->getPaymentState());
        self::assertCount(2, $transactionHistory->collection()->getAll());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testSynchronizeChangesWithPartialRefund(): void
    {
        // arrange
        $this->webhook = new Webhook(
            Amount::fromInt(1, Currency::getDefault()),
            EventCodes::REFUND,
            '2023-02-01T14:09:24+01:00',
            'coqCmt/IZ4E3CzPvMY8zTjQVL5hYJUiBRg8UU+iCWo0=',
            'TestMerchant',
            'merchantRef',
            '79140733813420890',
            (string)PaymentMethodCode::giftCard(),
            'reason',
            true,
            '',
            0,
            false,
            []
        );
        $transactionHistory = new TransactionHistory(
            'merchantRef', CaptureType::manual(), 1, Currency::getDefault(), null,
            [
                new HistoryItem(
                    '7914073381342284',
                    'merchantRef',
                    EventCodes::AUTHORISATION,
                    PaymentStates::STATE_PAID,
                    '2023-02-01T14:09:24+01:00',
                    true,
                    Amount::fromInt(222, Currency::getDefault()),
                    (string)PaymentMethodCode::giftCard(),
                    0,
                    false,
                    '',
                    CaptureType::immediate(),
                    1,
                    $this->timeProvider->getCurrentLocalTime()->getTimestamp()
                )
            ],
            '',
            '',
            []
        );
        $this->transactionHistoryRepository->setTransactionHistory($transactionHistory);
        $this->orderService->setMockOrderExists(true);

        // act
        StoreContext::doWithStore('1', [$this->service, 'synchronizeChanges'], [$this->webhook]);

        // assert
        $transactionHistory = $this->transactionService->getTransactionHistory($this->webhook->getMerchantReference());

        self::assertEquals(PaymentStates::STATE_PARTIALLY_REFUNDED, $transactionHistory->collection()->last()->getPaymentState());
        self::assertCount(2, $transactionHistory->collection()->getAll());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testSynchronizeChangesWithPartialRefundAndPartialCapture(): void
    {
        // arrange
        $this->webhook = new Webhook(
            Amount::fromInt(100, Currency::getDefault()),
            EventCodes::CAPTURE,
            '2023-02-01T14:09:24+01:00',
            'coqCmt/IZ4E3CzPvMY8zTjQVL5hYJUiBRg8UU+iCWo0=',
            'TestMerchant',
            'merchantRef',
            '79140733813420890',
            (string)PaymentMethodCode::giftCard(),
            'reason',
            true,
            '7914073381342284',
            0,
            false,
            []
        );
        $transactionHistory = new TransactionHistory(
            'merchantRef', CaptureType::manual(), 1, Currency::getDefault(), null,
            [
                new HistoryItem(
                    '7914073381342284',
                    'merchantRef',
                    EventCodes::AUTHORISATION,
                    PaymentStates::STATE_PAID,
                    '2023-02-01T14:09:24+01:00',
                    true,
                    Amount::fromInt(200, Currency::getDefault()),
                    (string)PaymentMethodCode::giftCard(),
                    0,
                    false,
                    '',
                    CaptureType::immediate(),
                    1,
                    $this->timeProvider->getCurrentLocalTime()->getTimestamp()
                ),
                new HistoryItem(
                    '791407338111342284',
                    'merchantRef',
                    EventCodes::CAPTURE,
                    PaymentStates::STATE_PAID,
                    '2023-02-01T14:09:24+01:00',
                    true,
                    Amount::fromInt(100, Currency::getDefault()),
                    (string)PaymentMethodCode::giftCard(),
                    0,
                    false,
                    '7914073381342284',
                    CaptureType::immediate(),
                    1,
                    $this->timeProvider->getCurrentLocalTime()->getTimestamp()
                ),
                new HistoryItem(
                    '79321407338111342284',
                    'merchantRef',
                    EventCodes::REFUND,
                    PaymentStates::STATE_REFUNDED,
                    '2023-02-01T14:09:24+01:00',
                    true,
                    Amount::fromInt(100, Currency::getDefault()),
                    (string)PaymentMethodCode::giftCard(),
                    0,
                    false,
                    '7914073381342284',
                    CaptureType::immediate(),
                    1,
                    $this->timeProvider->getCurrentLocalTime()->getTimestamp()
                )
            ],
            '',
            '',
            ['7914073381342284']
        );
        $this->transactionHistoryRepository->setTransactionHistory($transactionHistory);
        $this->orderService->setMockOrderExists(true);

        // act
        StoreContext::doWithStore('1', [$this->service, 'synchronizeChanges'], [$this->webhook]);

        // assert
        $transactionHistory = $this->transactionService->getTransactionHistory($this->webhook->getMerchantReference());

        self::assertEquals(PaymentStates::STATE_PARTIALLY_REFUNDED, $transactionHistory->collection()->last()->getPaymentState());
        self::assertCount(4, $transactionHistory->collection()->getAll());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testSynchronizeChangesToPaidAfterInitialPaymentFails(): void
    {
        // arrange
        $this->webhook = new Webhook(
            Amount::fromInt(100, Currency::getDefault()),
            EventCodes::AUTHORISATION,
            '2023-02-01T14:09:24+01:00',
            'coqCmt/IZ4E3CzPvMY8zTjQVL5hYJUiBRg8UU+iCWo0=',
            'TestMerchant',
            'merchantRef',
            '2',
            (string)PaymentMethodCode::giftCard(),
            'reason',
            true,
            '2',
            0,
            false,
            []
        );
        $transactionHistory = new TransactionHistory(
            'merchantRef', CaptureType::manual(), 1, Currency::getDefault(), null,
            [
                new HistoryItem(
                    '1',
                    'merchantRef',
                    EventCodes::AUTHORISATION,
                    PaymentStates::STATE_FAILED,
                    '2023-02-01T14:09:24+01:00',
                    true,
                    Amount::fromInt(200, Currency::getDefault()),
                    (string)PaymentMethodCode::giftCard(),
                    0,
                    false,
                    '1',
                    CaptureType::immediate(),
                    1,
                    $this->timeProvider->getCurrentLocalTime()->getTimestamp()
                )
            ],
            '',
            '',
            ['1']
        );
        $this->transactionHistoryRepository->setTransactionHistory($transactionHistory);
        $this->orderService->setMockOrderExists(true);

        // act
        StoreContext::doWithStore('1', [$this->service, 'synchronizeChanges'], [$this->webhook]);

        // assert
        $transactionHistory = $this->transactionService->getTransactionHistory($this->webhook->getMerchantReference());

        self::assertEquals(PaymentStates::STATE_PAID, $transactionHistory->collection()->last()->getPaymentState());
        self::assertCount(2, $transactionHistory->collection()->getAll());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testSynchronizeChangesWithTransactionHistoryWebhookSuccess(): void
    {
        //assert
        $this->webhook = new Webhook(
            Amount::fromInt(1, Currency::getDefault()),
            EventCodes::AUTHORISATION,
            '2023-02-01T14:09:24+01:00',
            'coqCmt/IZ4E3CzPvMY8zTjQVL5hYJUiBRg8UU+iCWo0=',
            'TestMerchant',
            'merchantRef',
            '7914073381342214',
            'Method',
            'reason',
            true,
            'ref',
            0,
            false,
            []
        );
        $transactionHistory = $this->expectedTransaction();
        $this->transactionHistoryRepository->setTransactionHistory($transactionHistory);
        $this->orderService->setMockOrderExists(true);

        //act
        StoreContext::doWithStore('1', [$this->service, 'synchronizeChanges'], [$this->webhook]);
        //assert
        $transactionHistory = $this->transactionService->getTransactionHistory($this->webhook->getMerchantReference());

        self::assertEquals(PaymentStates::STATE_PAID, $transactionHistory->collection()->last()->getPaymentState());
        self::assertCount(2, $transactionHistory->collection()->getAll());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testSynchronizeChangesNoTransactionHistoryWebhookFail(): void
    {
        //assert
        $this->webhook = new Webhook(
            Amount::fromInt(1, Currency::getDefault()),
            EventCodes::AUTHORISATION,
            '2023-02-01T14:09:24+01:00',
            'coqCmt/IZ4E3CzPvMY8zTjQVL5hYJUiBRg8UU+iCWo0=',
            'TestMerchant',
            'merchantRef',
            '7914073381342284',
            'Method',
            'reason',
            false,
            'ref',
            0,
            false,
            []
        );
        $this->orderService->setMockOrderExists(true);

        //act
        StoreContext::doWithStore('1', [$this->service, 'synchronizeChanges'], [$this->webhook]);
        //assert
        $transactionHistory = $this->transactionService->getTransactionHistory($this->webhook->getMerchantReference());

        self::assertEquals(PaymentStates::STATE_FAILED, $transactionHistory->collection()->last()->getPaymentState());
        self::assertCount(1, $transactionHistory->collection()->getAll());
    }

    /**
     * @return TransactionHistory
     *
     * @throws InvalidMerchantReferenceException
     */
    private function expectedTransaction(): TransactionHistory
    {
        return new TransactionHistory(
            'merchantRef', CaptureType::manual(), 1, Currency::getDefault(), null,
            [
                new HistoryItem(
                    '7914073381342284',
                    'merchantRef',
                    EventCodes::AUTHORISATION,
                    PaymentStates::STATE_PAID,
                    '2023-02-01T14:09:24+01:00',
                    true,
                    Amount::fromInt(1, Currency::getDefault()),
                    (string)PaymentMethodCode::giftCard(),
                    0,
                    false,
                    '7914073381342284',
                    CaptureType::immediate(),
                    $this->timeProvider->getCurrentLocalTime()->getTimestamp()
                )
            ],
            '',
            '',
            ['7914073381342284']
        );
    }

    /**
     * @return TransactionHistory
     *
     * @throws InvalidMerchantReferenceException
     */
    private function transactionHistory(): TransactionHistory
    {
        return new TransactionHistory('merchantRef', CaptureType::immediate(), 0, null,
            AuthorizationType::finalAuthorization(),
            [
                new HistoryItem(
                    'PAYMENT_REQUESTED_originalPsp',
                    'merchantRef',
                    'PAYMENT_REQUESTED',
                    'paymentState',
                    'date',
                    true,
                    Amount::fromInt(1, Currency::getDefault()),
                    'paymentMethod',
                    0,
                    false
                ),
                new HistoryItem(
                    'originalPsp',
                    'merchantRef',
                    EventCodes::AUTHORISATION,
                    'paymentState',
                    'date',
                    true,
                    Amount::fromInt(1, Currency::getDefault()),
                    'paymentMethod',
                    0,
                    false
                ),
                new HistoryItem(
                    'pspRef1',
                    'merchantRef',
                    'CODE1',
                    'paymentState',
                    'date',
                    true,
                    Amount::fromInt(1, Currency::getDefault()),
                    'paymentMethod',
                    0,
                    false
                ),
                new HistoryItem(
                    'pspRef1',
                    'merchantRef',
                    'CODE1',
                    'paymentState',
                    'date',
                    false,
                    Amount::fromInt(1, Currency::getDefault()),
                    'paymentMethod',
                    0,
                    false
                ),
                new HistoryItem(
                    'pspRef1',
                    'merchantRef',
                    'CODE2',
                    'paymentState',
                    'date',
                    true,
                    Amount::fromInt(1, Currency::getDefault()),
                    'paymentMethod',
                    0,
                    false
                ),
                new HistoryItem(
                    'pspRef2',
                    'merchantRef',
                    'CODE2',
                    'paymentState',
                    'date',
                    true,
                    Amount::fromInt(1, Currency::getDefault()),
                    'paymentMethod',
                    0,
                    false
                ),
                new HistoryItem(
                    'pspRef3',
                    'merchantRef',
                    'CODE1',
                    'paymentState',
                    'date',
                    false,
                    Amount::fromInt(1, Currency::getDefault()),
                    'paymentMethod3',
                    0,
                    false
                ),
                new HistoryItem(
                    'pspRef4',
                    'merchantRef',
                    'CODE1',
                    'paymentState2',
                    'date',
                    false,
                    Amount::fromInt(1, Currency::getDefault()),
                    'paymentMethod',
                    0,
                    false
                ),
                new HistoryItem(
                    'pspRef1',
                    'merchantRef',
                    'CODE3',
                    'paymentState',
                    'date',
                    false,
                    Amount::fromInt(1, Currency::getDefault()),
                    'paymentMethod',
                    0,
                    false
                ),
                new HistoryItem(
                    'pspRef5',
                    'merchantRef',
                    'CODE3',
                    'paymentState',
                    'date',
                    true,
                    Amount::fromInt(1, Currency::getDefault()),
                    'paymentMethod',
                    0,
                    false
                ),
                new HistoryItem(
                    'pspRef1',
                    'merchantRef',
                    'CODE6',
                    'paymentState',
                    'date',
                    true,
                    Amount::fromInt(1, Currency::getDefault()),
                    'paymentMethod',
                    0,
                    false
                )
            ]
        );
    }
}
