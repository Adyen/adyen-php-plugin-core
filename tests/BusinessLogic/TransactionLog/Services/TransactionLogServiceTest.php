<?php

namespace Adyen\Core\Tests\BusinessLogic\TransactionLog\Services;

use Adyen\Core\BusinessLogic\Bootstrap\SingleInstance;
use Adyen\Core\BusinessLogic\DataAccess\TransactionLog\Entities\TransactionLog;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Currency;
use Adyen\Core\BusinessLogic\Domain\Webhook\Models\Webhook;
use Adyen\Core\BusinessLogic\TransactionLog\Repositories\TransactionLogRepository;
use Adyen\Core\BusinessLogic\TransactionLog\Services\TransactionLogService;
use Adyen\Core\BusinessLogic\Webhook\Tasks\OrderUpdateTask;
use Adyen\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException;
use Adyen\Core\Infrastructure\TaskExecution\QueueItem;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Adyen\Core\Tests\BusinessLogic\TransactionLog\Mocks\MockTransactionLogRepository;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\FooTask;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class TransactionLogServiceTest
 *
 * @package Adyen\Core\Tests\BusinessLogic\TransactionLog\Services
 */
class TransactionLogServiceTest extends BaseTestCase
{
    /**
     * @var TransactionLogRepository
     */
    private $repository;

    /**
     * @var TransactionLogService
     */
    private $service;

    /**
     * @var Webhook
     */
    private $webhook;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new MockTransactionLogRepository();
        TestServiceRegister::registerService(
            TransactionLogRepository::class,
            new SingleInstance(function () {
                return $this->repository;
            })
        );

        $this->service = TestServiceRegister::getService(TransactionLogService::class);
        $this->webhook = new Webhook(
            Amount::fromInt(1, Currency::getDefault()),
            'code',
            '2021-01-01T01:00:00+01:00',
            'hmac',
            'mc',
            '1',
            'psp',
            'method',
            'r',
            true,
            'originalRef',
            0,
            false
        );
    }

    /**
     * @return void
     *
     * @throws QueueItemDeserializationException
     */
    public function testCreateTaskNonTransactional(): void
    {
        // arrange
        $item = new QueueItem(new FooTask());

        // act
        $this->service->create($item);

        // assert
        self::assertNull($this->repository->getTransactionLog('1'));
    }

    /**
     * @throws QueueItemDeserializationException
     */
    public function testCreate(): void
    {
        // arrange
        $task = new OrderUpdateTask($this->webhook);
        $item = new QueueItem($task);

        // act
        $this->service->create($item);

        // assert
        $log = $this->repository->getTransactionLog('1');
        self::assertEquals($this->webhook->getPaymentMethod(), $log->getPaymentMethod());
        self::assertEquals($this->webhook->getMerchantReference(), $log->getMerchantReference());
        self::assertEquals($this->webhook->getReason(), $log->getReason());
        self::assertEquals((\DateTime::createFromFormat(\DateTimeInterface::ATOM, $this->webhook->getEventDate()))->getTimestamp(), $log->getTimestamp());
        self::assertEquals($this->webhook->getEventCode(), $log->getEventCode());
        self::assertEquals($this->webhook->isSuccess(), $log->isSuccessful());
        self::assertEquals(QueueItem::QUEUED, $log->getQueueStatus());
        self::assertNull($log->getFailureDescription());
        self::assertEquals($task->getTransactionLog(), $log);
    }

    /**
     * @throws QueueItemDeserializationException
     */
    public function testUpdate(): void
    {
        // arrange
        $task = new OrderUpdateTask($this->webhook);
        $item = new QueueItem($task);

        // act
        $this->service->create($item);
        $log = $this->repository->getTransactionLog('1');
        $log->setQueueStatus(QueueItem::FAILED);
        $log->setFailureDescription('FAILURE');
        $this->service->save($log);

        // assert
        $log = $this->repository->getTransactionLog('1');
        self::assertEquals($this->webhook->getPaymentMethod(), $log->getPaymentMethod());
        self::assertEquals($this->webhook->getMerchantReference(), $log->getMerchantReference());
        self::assertEquals($this->webhook->getReason(), $log->getReason());
        self::assertEquals((\DateTime::createFromFormat(\DateTimeInterface::ATOM, $this->webhook->getEventDate()))->getTimestamp(), $log->getTimestamp());
        self::assertEquals($this->webhook->getEventCode(), $log->getEventCode());
        self::assertEquals($this->webhook->isSuccess(), $log->isSuccessful());
        self::assertEquals(QueueItem::FAILED, $log->getQueueStatus());
        self::assertEquals('FAILURE', $log->getFailureDescription());
        self::assertEquals($task->getTransactionLog(), $log);
    }

    /**
     * @throws QueueItemDeserializationException
     */
    public function testLoad(): void
    {
        // arrange
        $task = new OrderUpdateTask($this->webhook);
        $task->setExecutionId(1);
        $log = new TransactionLog();
        $log->setExecutionId(1);
        $log->setIsSuccessful($this->webhook->isSuccess());
        $log->setReason($this->webhook->getReason());
        $log->setEventCode($this->webhook->getEventCode());
        $log->setPaymentMethod($this->webhook->getPaymentMethod());
        $log->setTimestamp((\DateTime::createFromFormat(\DateTimeInterface::ATOM, '2021-01-01T01:00:00+01:00'))->getTimestamp());
        $log->setQueueStatus(QueueItem::QUEUED);
        $log->setMerchantReference($this->webhook->getMerchantReference());
        $this->repository->setTransactionLog($log);
        $item = new QueueItem($task);
        $item->setId(1);

        // act
        $this->service->load($item);

        // assert
        self::assertEquals($task->getTransactionLog(), $log);
        self::assertEquals($this->webhook->getPaymentMethod(), $task->getTransactionLog()->getPaymentMethod());
        self::assertEquals($this->webhook->getMerchantReference(), $task->getTransactionLog()->getMerchantReference());
        self::assertEquals($this->webhook->getReason(), $task->getTransactionLog()->getReason());
        self::assertEquals((\DateTime::createFromFormat(\DateTimeInterface::ATOM, $this->webhook->getEventDate()))->getTimestamp(), $task->getTransactionLog()->getTimestamp());
        self::assertEquals($this->webhook->getEventCode(), $task->getTransactionLog()->getEventCode());
        self::assertEquals($this->webhook->isSuccess(), $task->getTransactionLog()->isSuccessful());
        self::assertEquals(QueueItem::QUEUED, $task->getTransactionLog()->getQueueStatus());
        self::assertNull($log->getFailureDescription());
    }
}
