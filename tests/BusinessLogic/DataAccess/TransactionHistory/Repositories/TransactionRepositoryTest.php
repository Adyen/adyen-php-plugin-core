<?php

namespace Adyen\Core\Tests\BusinessLogic\DataAccess\TransactionHistory\Repositories;

use Adyen\Core\BusinessLogic\DataAccess\TransactionHistory\Entities\TransactionHistory as TransactionEntity;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Currency;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Models\CaptureType;
use Adyen\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models\HistoryItem;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory as TransactionModel;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Repositories\TransactionHistoryRepository;
use Adyen\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use Adyen\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;
use Exception;

/**
 * Class TransactionRepositoryTest
 *
 * @package Adyen\Core\Tests\BusinessLogic\DataAccess\TransactionHistoryHistory\Repositories
 */
class TransactionRepositoryTest extends BaseTestCase
{
    /**
     * @var RepositoryInterface
     */
    private $repository;

    /**
     * @var TransactionHistoryRepository
     */
    private $transactionRepository;

    /**
     * @throws RepositoryNotRegisteredException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = TestRepositoryRegistry::getRepository(TransactionEntity::getClassName());
        $this->transactionRepository = TestServiceRegister::getService(TransactionHistoryRepository::class);
    }

    /**
     * @throws Exception
     */
    public function testGetTransactionNoTransaction(): void
    {
        // act
        $result = StoreContext::doWithStore('1', [$this->transactionRepository, 'getTransactionHistory'], ['11']);

        // assert
        self::assertNull($result);
    }

    /**
     * @throws Exception
     */
    public function testGetTransaction(): void
    {
        // arrange
        $transaction = new TransactionModel('merchantReference', CaptureType::manual(), 0, Currency::getDefault(),
            $this->historyItems());
        $transactionEntity = new TransactionEntity();
        $transactionEntity->setTransactionHistory($transaction);
        $transactionEntity->setStoreId('1');
        $transactionEntity->setMerchantReference('merchantReference');
        $this->repository->save($transactionEntity);

        // act
        $result = StoreContext::doWithStore('1', [$this->transactionRepository, 'getTransactionHistory'],
            ['merchantReference']);

        // assert
        self::assertEquals($transaction, $result);
    }

    /**
     * @throws Exception
     */
    public function testGetTransactionForDifferentStore(): void
    {
        // arrange
        $transaction = new TransactionModel('merchantReference', CaptureType::manual(), 0, Currency::getDefault(),
            $this->historyItems());
        $transactionEntity = new TransactionEntity();
        $transactionEntity->setTransactionHistory($transaction);
        $transactionEntity->setStoreId('1');
        $transactionEntity->setMerchantReference('merchantReference');
        $this->repository->save($transactionEntity);

        // act
        $result = StoreContext::doWithStore('2', [$this->transactionRepository, 'getTransactionHistory'],
            ['merchantReference']);

        // assert
        self::assertNull($result);
    }

    /**
     * @throws Exception
     */
    public function testGetTransactionWithWrongMerchantReference(): void
    {
        // arrange
        $transaction = new TransactionModel('merchantReference', CaptureType::manual(), 0, Currency::getDefault(),
            $this->historyItems());
        $transactionEntity = new TransactionEntity();
        $transactionEntity->setTransactionHistory($transaction);
        $transactionEntity->setStoreId('1');
        $transactionEntity->setMerchantReference('merchantReference');
        $this->repository->save($transactionEntity);

        // act
        $result = StoreContext::doWithStore('1', [$this->transactionRepository, 'getTransactionHistory'],
            ['merchantReference1']);

        // assert
        self::assertNull($result);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testSetTransaction(): void
    {
        // arrange
        $transaction = new TransactionModel('merchantReference', CaptureType::manual(), 0, Currency::getDefault(),
            $this->historyItems());

        // act
        StoreContext::doWithStore('1', [$this->transactionRepository, 'setTransactionHistory'], [$transaction]);

        // assert
        $savedEntity = $this->repository->select();
        self::assertEquals($transaction, $savedEntity[0]->getTransactionHistory());
    }

    /**     *
     * @throws Exception
     */
    public function testSetTransactionAlreadyExists(): void
    {
        // arrange
        $transaction = new TransactionModel('merchantReference', CaptureType::manual(), 0, Currency::getDefault(),
            $this->historyItems());
        $transactionEntity = new TransactionEntity();
        $transactionEntity->setTransactionHistory($transaction);
        $transactionEntity->setStoreId('1');
        $transactionEntity->setMerchantReference('merchantReference');
        $this->repository->save($transactionEntity);
        $newTransaction = new TransactionModel('merchantReference', CaptureType::manual(), 0, Currency::getDefault(),
            $this->historyItems());

        // act
        StoreContext::doWithStore('1', [$this->transactionRepository, 'setTransactionHistory'], [$newTransaction]);

        // assert
        $savedEntity = $this->repository->selectOne();
        self::assertEquals($newTransaction, $savedEntity->getTransactionHistory());
    }

    /**
     * @throws Exception
     */
    public function testSetSettingsAlreadyExistsForOtherStore(): void
    {
        // arrange
        $transaction = new TransactionModel('merchantReference', CaptureType::manual(), 0, Currency::getDefault(),
            $this->historyItems());
        $transactionEntity = new TransactionEntity();
        $transactionEntity->setTransactionHistory($transaction);
        $transactionEntity->setStoreId('1');
        $transactionEntity->setMerchantReference('merchantReference');
        $this->repository->save($transactionEntity);
        $newTransaction = new TransactionModel('merchantReference', CaptureType::manual(), 0, Currency::getDefault(),
            $this->historyItems());

        // act
        StoreContext::doWithStore('2', [$this->transactionRepository, 'setTransactionHistory'], [$newTransaction]);

        // assert
        $savedEntity = $this->repository->select();
        self::assertCount(2, $savedEntity);
        self::assertEquals($transaction, $savedEntity[0]->getTransactionHistory());
        self::assertEquals($newTransaction, $savedEntity[1]->getTransactionHistory());
    }

    /**
     * @return HistoryItem[]
     */
    private function historyItems(): array
    {
        return [
            new HistoryItem(
                'psp1',
                'merchantReference',
                'code1',
                'state1',
                'date1',
                'status1',
                Amount::fromInt(1111, Currency::getDefault()),
                'visa',
                0,
                false
            ),
            new HistoryItem(
                'psp2',
                'merchantReference',
                'code2',
                'state2',
                'date2',
                'status2',
                Amount::fromInt(1111, Currency::getDefault()),
                'visa',
                0,
                false
            ),
            new HistoryItem(
                'psp3',
                'merchantReference',
                'code3',
                'state3',
                'date3',
                'status3',
                Amount::fromInt(1111, Currency::getDefault()),
                'visa',
                0,
                false
            )
        ];
    }
}
