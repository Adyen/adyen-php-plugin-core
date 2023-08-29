<?php

namespace Adyen\Core\Tests\BusinessLogic\Domain\TransactionHistory\Services;

use Adyen\Core\BusinessLogic\Bootstrap\SingleInstance;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Currency;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Models\CaptureType;
use Adyen\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models\HistoryItem;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Repositories\TransactionHistoryRepository;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Services\TransactionHistoryService;
use Adyen\Core\BusinessLogic\Domain\Webhook\Models\Webhook;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Adyen\Core\Tests\BusinessLogic\Domain\TransactionHistory\MockComponents\MockTransactionRepository;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;
use Exception;

/**
 * Class TransactionServiceTest
 *
 * @package Adyen\Core\Tests\BusinessLogic\Domain\TransactionHistoryHistory
 */
class TransactionServiceTest extends BaseTestCase
{
    /**
     * @var TransactionHistoryRepository
     */
    private $repository;

    /**
     * @var TransactionHistoryService
     */
    private $service;

    /**
     * @var Webhook
     */
    private $webhook;

    protected function setUp(): void
    {
        parent::setUp();

        $this->webhook = new Webhook(
            Amount::fromInt(1, Currency::getDefault()),
            'CODE1',
            'data',
            '',
            '',
            'merchantRef',
            'pspRef1',
            '',
            '',
            true,
            'originalPsp',
            0,
            false
        );
        $this->repository = new MockTransactionRepository();
        TestServiceRegister::registerService(
            TransactionHistoryRepository::class,
            new SingleInstance(function () {
                return $this->repository;
            })
        );

        $this->service = TestServiceRegister::getService(TransactionHistoryService::class);
    }

    /**
     * @throws Exception
     */
    public function testGetTransactionHistoryNull(): void
    {
        // act
        $this->repository->setTransactionHistory(null);
        $result = StoreContext::doWithStore(
            '1',
            [$this->service, 'getTransactionHistory'],
            [$this->webhook->getMerchantReference()]
        );

        // assert
        self::assertEquals(
            $result,
            new TransactionHistory('merchantRef', CaptureType::immediate(), 0, Currency::getDefault())
        );
    }

    /**
     * @throws Exception
     */
    public function testGetTransactionHistory(): void
    {
        // act
        $transaction = $this->transactionHistory();
        $this->repository->setTransactionHistory($transaction);
        $result = StoreContext::doWithStore(
            '1',
            [$this->service, 'getTransactionHistory'],
            [$this->webhook->getMerchantReference()]
        );

        // assert
        self::assertEquals($transaction, $result);
    }

    /**
     * @throws Exception
     */
    public function testSetTransactionHistory(): void
    {
        // act
        $transaction = $this->transactionHistory();

        StoreContext::doWithStore('1', [$this->service, 'setTransactionHistory'], [$transaction]);

        // assert
        self::assertEquals($this->repository->getTransactionHistory('1'), $transaction);
    }

    /**
     * @return TransactionHistory
     */
    private function transactionHistory(): TransactionHistory
    {
        return new TransactionHistory('merchantRef', CaptureType::manual(), 0, null, [
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
