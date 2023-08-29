<?php

namespace Adyen\Core\Tests\BusinessLogic\DataAccess\TransactionLog\Repositories;

use Adyen\Core\BusinessLogic\DataAccess\TransactionLog\Entities\TransactionLog;
use Adyen\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Adyen\Core\BusinessLogic\TransactionLog\Repositories\TransactionLogRepository;
use Adyen\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use Adyen\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use Adyen\Core\Infrastructure\TaskExecution\QueueItem;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;
use Exception;

/**
 * Class TransactionLogRepositoryTest
 *
 * @package Adyen\Core\Tests\BusinessLogic\DataAccess\TransactionLog\Repositories
 */
class TransactionLogRepositoryTest extends BaseTestCase
{
    /**
     * @var RepositoryInterface
     */
    private $repository;

    /**
     * @var TransactionLogRepository
     */
    private $transactionLogRepository;

    /**
     * @throws RepositoryNotRegisteredException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = TestRepositoryRegistry::getRepository(TransactionLog::getClassName());
        $this->transactionLogRepository = TestServiceRegister::getService(TransactionLogRepository::class);
    }

    /**
     * @throws Exception
     */
    public function testGetLogNoLog(): void
    {
        // act
        $result = StoreContext::doWithStore('1', [$this->transactionLogRepository, 'getTransactionLog'], ['11']);

        // assert
        self::assertNull($result);
    }

    /**
     * @throws Exception
     */
    public function testGetTransaction(): void
    {
        // arrange
        $transactionEntity = $this->generateLogEntity();
        $this->repository->save($transactionEntity);
        // act
        $result = StoreContext::doWithStore('store1', [$this->transactionLogRepository, 'getTransactionLog'], ['merch']
        );

        // assert
        self::assertEquals($transactionEntity, $result);
    }

    /**
     * @throws Exception
     */
    public function testGetLogForDifferentStore(): void
    {
        // arrange
        $transactionEntity = $this->generateLogEntity();
        $this->repository->save($transactionEntity);

        // act
        $result = StoreContext::doWithStore('store2', [$this->transactionLogRepository, 'getTransactionLog'], ['merch']
        );

        // assert
        self::assertNull($result);
    }

    /**
     * @throws Exception
     */
    public function testGetLogForDifferentMerchantReference(): void
    {
        // arrange
        $transactionEntity = $this->generateLogEntity();
        $this->repository->save($transactionEntity);

        // act
        $result = StoreContext::doWithStore(
            'store1',
            [$this->transactionLogRepository, 'getTransactionLog'],
            ['merch11']
        );

        // assert
        self::assertNull($result);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testSetLog(): void
    {
        // arrange
        $transactionLog = $this->generateLogEntity();

        // act
        StoreContext::doWithStore('store1', [$this->transactionLogRepository, 'setTransactionLog'], [$transactionLog]);

        // assert
        $savedEntity = $this->repository->select();
        self::assertEquals($transactionLog, $savedEntity[0]);
    }

    /**
     * @throws Exception
     */
    public function testSetSettingsAlreadyExistsForOtherStore(): void
    {
        // arrange
        $transactionLog = $this->generateLogEntity();
        $this->repository->save($transactionLog);
        $newTransaction = $this->generateLogEntity2();

        // act
        StoreContext::doWithStore('store2', [$this->transactionLogRepository, 'setTransactionLog'], [$newTransaction]);

        // assert
        /** @var TransactionLog[] $savedEntity */
        $savedEntity = $this->repository->select();
        self::assertCount(2, $savedEntity);
        self::assertEquals($newTransaction->isSuccessful(), $savedEntity[1]->isSuccessful());
        self::assertEquals($newTransaction->getTimestamp(), $savedEntity[1]->getTimestamp());
        self::assertEquals($newTransaction->getQueueStatus(), $savedEntity[1]->getQueueStatus());
        self::assertEquals($newTransaction->getReason(), $savedEntity[1]->getReason());
        self::assertEquals($newTransaction->getEventCode(), $savedEntity[1]->getEventCode());
        self::assertEquals($newTransaction->getPaymentMethod(), $savedEntity[1]->getPaymentMethod());
        self::assertEquals($newTransaction->getFailureDescription(), $savedEntity[1]->getFailureDescription());
        self::assertEquals($transactionLog->isSuccessful(), $savedEntity[0]->isSuccessful());
        self::assertEquals($transactionLog->getTimestamp(), $savedEntity[0]->getTimestamp());
        self::assertEquals($transactionLog->getQueueStatus(), $savedEntity[0]->getQueueStatus());
        self::assertEquals($transactionLog->getReason(), $savedEntity[0]->getReason());
        self::assertEquals($transactionLog->getEventCode(), $savedEntity[0]->getEventCode());
        self::assertEquals($transactionLog->getPaymentMethod(), $savedEntity[0]->getPaymentMethod());
        self::assertEquals($transactionLog->getFailureDescription(), $savedEntity[0]->getFailureDescription());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetItemByExecutionId(): void
    {
        // arrange
        $transactionLog1 = $this->generateLogEntity();
        $this->repository->save($transactionLog1);
        $transactionLog2 = $this->generateLogEntity2();
        $this->repository->save($transactionLog2);

        // act
        $result1 = StoreContext::doWithStore(
            'store1',
            [$this->transactionLogRepository, 'getItemByExecutionId'],
            [11]
        );
        $result2 = StoreContext::doWithStore(
            'store1',
            [$this->transactionLogRepository, 'getItemByExecutionId'],
            [22]
        );
        // assert
        self::assertEquals($transactionLog1->isSuccessful(), $result1->isSuccessful());
        self::assertEquals($transactionLog1->getTimestamp(), $result1->getTimestamp());
        self::assertEquals($transactionLog1->getQueueStatus(), $result1->getQueueStatus());
        self::assertEquals($transactionLog1->getReason(), $result1->getReason());
        self::assertEquals($transactionLog1->getEventCode(), $result1->getEventCode());
        self::assertEquals($transactionLog1->getPaymentMethod(), $result1->getPaymentMethod());
        self::assertEquals($transactionLog1->getFailureDescription(), $result1->getFailureDescription());
        self::assertEquals($transactionLog2->isSuccessful(), $result2->isSuccessful());
        self::assertEquals($transactionLog2->getTimestamp(), $result2->getTimestamp());
        self::assertEquals($transactionLog2->getQueueStatus(), $result2->getQueueStatus());
        self::assertEquals($transactionLog2->getReason(), $result2->getReason());
        self::assertEquals($transactionLog2->getEventCode(), $result2->getEventCode());
        self::assertEquals($transactionLog2->getPaymentMethod(), $result2->getPaymentMethod());
        self::assertEquals($transactionLog2->getFailureDescription(), $result2->getFailureDescription());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testCount(): void
    {
        // arrange
        $transactionLog1 = $this->generateLogEntity();
        $this->repository->save($transactionLog1);
        $transactionLog2 = $this->generateLogEntity2();
        $this->repository->save($transactionLog2);

        // act
        $result1 = StoreContext::doWithStore(
            'store1',
            [$this->transactionLogRepository, 'count']
        );
        // assert
        self::assertEquals(2, $result1);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testFind(): void
    {
        // arrange
        $transactionLog1 = $this->generateLogEntity();
        $this->repository->save($transactionLog1);
        $transactionLog2 = $this->generateLogEntity2();
        $this->repository->save($transactionLog2);

        // act
        $result1 = StoreContext::doWithStore(
            'store1',
            [$this->transactionLogRepository, 'find'],
            [10, 0]
        );
        // assert
        self::assertEquals([$transactionLog2, $transactionLog1], $result1);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testUpdate(): void
    {
        // arrange
        $transactionEntity = $this->generateLogEntity();
        $this->repository->save($transactionEntity);
        $transactionEntity->setQueueStatus(QueueItem::COMPLETED);

        // act
        StoreContext::doWithStore(
            'store1',
            [$this->transactionLogRepository, 'updateTransactionLog'],
            [$transactionEntity]
        );
        // assert
        $log = $this->transactionLogRepository->getItemByExecutionId($transactionEntity->getExecutionId());
        $old = $this->generateLogEntity();
        self::assertEquals($log->getExecutionId(), $old->getExecutionId());
        self::assertEquals($log->getMerchantReference(), $old->getMerchantReference());
        self::assertEquals($log->getEventCode(), $old->getEventCode());
        self::assertEquals($log->getReason(), $old->getReason());
        self::assertEquals($log->getPspReference(), $old->getPspReference());
        self::assertEquals($log->getPaymentMethod(), $old->getPaymentMethod());
        self::assertNotEquals($log->getQueueStatus(), $old->getQueueStatus());
        self::assertEquals(QueueItem::COMPLETED, $log->getQueueStatus());
    }

    /**
     * @return TransactionLog
     */
    private function generateLogEntity(): TransactionLog
    {
        $transactionEntity = new TransactionLog();
        $transactionEntity->setStoreId('store1');
        $transactionEntity->setMerchantReference('merch');
        $transactionEntity->setExecutionId(11);
        $transactionEntity->setReason('reason');
        $transactionEntity->setIsSuccessful(true);
        $transactionEntity->setEventCode('code');
        $transactionEntity->setTimestamp((\DateTime::createFromFormat(\DateTimeInterface::ATOM, '2021-01-01T01:00:00+01:00'))->getTimestamp());
        $transactionEntity->setFailureDescription('failure');
        $transactionEntity->setQueueStatus(QueueItem::QUEUED);
        $transactionEntity->setPaymentMethod('method');
        $transactionEntity->setPspReference('PSP');

        return $transactionEntity;
    }

    /**
     * @return TransactionLog
     */
    private function generateLogEntity2(): TransactionLog
    {
        $transactionEntity = new TransactionLog();
        $transactionEntity->setStoreId('store1');
        $transactionEntity->setMerchantReference('merch');
        $transactionEntity->setExecutionId(22);
        $transactionEntity->setReason('reason2');
        $transactionEntity->setIsSuccessful(true);
        $transactionEntity->setEventCode('code2');
        $transactionEntity->setTimestamp((\DateTime::createFromFormat(\DateTimeInterface::ATOM, '2021-01-01T01:00:00+01:00'))->getTimestamp());
        $transactionEntity->setFailureDescription('failure2');
        $transactionEntity->setQueueStatus(QueueItem::IN_PROGRESS);
        $transactionEntity->setPaymentMethod('method2');

        return $transactionEntity;
    }
}
