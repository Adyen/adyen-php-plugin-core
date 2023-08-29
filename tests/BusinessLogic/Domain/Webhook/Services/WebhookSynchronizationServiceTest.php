<?php

namespace Adyen\Core\Tests\BusinessLogic\Domain\Webhook\Services;

use Adyen\Core\BusinessLogic\Bootstrap\SingleInstance;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Currency;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Models\CaptureType;
use Adyen\Core\BusinessLogic\Domain\Integration\Order\OrderService;
use Adyen\Core\BusinessLogic\Domain\Integration\Store\StoreService;
use Adyen\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Exceptions\InvalidMerchantReferenceException;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models\HistoryItem;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Repositories\TransactionHistoryRepository;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Services\TransactionHistoryService;
use Adyen\Core\BusinessLogic\Domain\Webhook\Models\Webhook;
use Adyen\Core\BusinessLogic\Domain\Webhook\Services\WebhookSynchronizationService;
use Adyen\Core\BusinessLogic\Webhook\Services\OrderStatusMappingService;
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
            false
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
        self::assertTrue($result);
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
            false
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
            false
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
            'Method',
            'reason',
            true,
            'oRef',
            0,
            false
        );

        // act
        StoreContext::doWithStore('1', [$this->service, 'synchronizeChanges'], [$this->webhook]);

        // assert
        $transaction = $this->transactionService->getTransactionHistory($this->webhook->getMerchantReference());

        self::assertEquals($this->expectedTransaction(), $transaction);
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
            'Method',
            'reason',
            true,
            'ref',
            0,
            false
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
            'Method',
            'reason',
            false,
            'ref',
            0,
            false
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
            false
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
            false
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
            'merchantRef', CaptureType::manual(), 1, Currency::getDefault(),
        [new HistoryItem(
            '7914073381342284',
            'merchantRef',
            EventCodes::AUTHORISATION,
            PaymentStates::STATE_PAID,
            '2023-02-01T14:09:24+01:00',
            true,
            Amount::fromInt(1, Currency::getDefault()),
            'Method',
            0,
            false
        )]
        );
    }

    /**
     * @return TransactionHistory
     */
    private function transactionHistory(): TransactionHistory
    {
        return new TransactionHistory('merchantRef', CaptureType::immediate(), 0, null, [
                new HistoryItem(
                    'originalPsp',
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
