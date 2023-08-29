<?php

namespace Adyen\Core\Tests\BusinessLogic\TransactionLog\Listeners;

use Adyen\Core\BusinessLogic\Bootstrap\SingleInstance;
use Adyen\Core\BusinessLogic\DataAccess\TransactionLog\Entities\TransactionLog;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Currency;
use Adyen\Core\BusinessLogic\Domain\Webhook\Models\Webhook;
use Adyen\Core\BusinessLogic\TransactionLog\Listeners\LoadListener;
use Adyen\Core\BusinessLogic\TransactionLog\Repositories\TransactionLogRepository;
use Adyen\Core\BusinessLogic\TransactionLog\Services\TransactionLogService;
use Adyen\Core\BusinessLogic\Webhook\Tasks\OrderUpdateTask;
use Adyen\Core\Infrastructure\TaskExecution\Events\QueueItemStartedEvent;
use Adyen\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException;
use Adyen\Core\Infrastructure\TaskExecution\QueueItem;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Adyen\Core\Tests\BusinessLogic\TransactionLog\Mocks\MockTransactionLogRepository;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class LoadListenerTest
 *
 * @package Adyen\Core\Tests\BusinessLogic\TransactionLog\Listeners
 */
class LoadListenerTest extends BaseTestCase
{
    /**
     * @var LoadListener
     */
    protected $listener;

    /**
     * @var Webhook
     */
    private $webhook;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->listener = new LoadListener(TestServiceRegister::getService(TransactionLogService::class));
        $this->repository = TestServiceRegister::getService(TransactionLogRepository::class);
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
     * @throws QueueItemDeserializationException
     */
    public function testAborted(): void
    {
        // arrange
        $log = new TransactionLog();
        $log->setExecutionId(1);
        $log->setIsSuccessful($this->webhook->isSuccess());
        $log->setReason($this->webhook->getReason());
        $log->setEventCode($this->webhook->getEventCode());
        $log->setPaymentMethod($this->webhook->getPaymentMethod());
        $log->setTimestamp((\DateTime::createFromFormat(\DateTimeInterface::ATOM, $this->webhook->getEventDate()))->getTimestamp());
        $log->setQueueStatus(QueueItem::QUEUED);
        $log->setMerchantReference($this->webhook->getMerchantReference());
        $this->repository->setTransactionLog($log);
        $orderUpdateTask = new OrderUpdateTask($this->webhook);
        $item = new QueueItem($orderUpdateTask);
        $item->setStatus(QueueItem::QUEUED);
        $item->setId(1);
        $event = new QueueItemStartedEvent($item);

        // act
        $this->listener->handle($event);

        // assert
        self::assertEquals($orderUpdateTask->getTransactionLog(), $log);
    }
}
