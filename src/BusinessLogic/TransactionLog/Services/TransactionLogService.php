<?php

namespace Adyen\Core\BusinessLogic\TransactionLog\Services;

use Adyen\Core\BusinessLogic\DataAccess\TransactionLog\Entities\TransactionLog;
use Adyen\Core\BusinessLogic\Domain\Disconnect\Repositories\DisconnectRepository;
use Adyen\Core\BusinessLogic\Domain\Integration\Order\OrderService;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Exceptions\InvalidMerchantReferenceException;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Services\TransactionHistoryService;
use Adyen\Core\BusinessLogic\TransactionLog\Contracts\TransactionLogAware;
use Adyen\Core\BusinessLogic\TransactionLog\Repositories\TransactionLogRepository;
use Adyen\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use Adyen\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException;
use Adyen\Core\Infrastructure\TaskExecution\QueueItem;
use Adyen\Core\Infrastructure\TaskExecution\Task;
use DateTime;
use DateTimeInterface;
use Exception;

/**
 * Class TransactionLogService
 *
 * @package Adyen\Core\BusinessLogic\TransactionLog\Services
 */
class TransactionLogService
{
    /**
     * @var TransactionHistoryService
     */
    private $transactionHistoryService;
    /**
     * @var TransactionLogRepository
     */
    private $transactionLogRepository;
    /**
     * @var OrderService
     */
    private $orderService;
    /**
     * @var DisconnectRepository
     */
    private $disconnectRepository;

    public function __construct(
        TransactionHistoryService $transactionHistoryService,
        TransactionLogRepository  $transactionLogRepository,
        OrderService              $orderService,
        DisconnectRepository      $disconnectRepository
    )
    {

        $this->transactionHistoryService = $transactionHistoryService;
        $this->transactionLogRepository = $transactionLogRepository;
        $this->orderService = $orderService;
        $this->disconnectRepository = $disconnectRepository;
    }

    /**
     * Creates transaction log. Fails existing.
     *
     * @param QueueItem $item
     *
     * @return void
     *
     * @throws QueueItemDeserializationException|InvalidMerchantReferenceException
     */
    public function create(QueueItem $item): void
    {
        /** @var Task | TransactionLogAware $task */
        $task = $item->getTask();
        if ($task === null) {
            return;
        }

        if (!($task instanceof TransactionLogAware)) {
            return;
        }

        if ($item->getParentId() !== null) {
            return;
        }

        if ($item->getId() && ($log = $this->transactionLogRepository->getItemByExecutionId($item->getId())) !== null) {
            $log->setQueueStatus(QueueItem::FAILED);
            $this->update($log);
        }

        $transactionLog = $this->createTransactionLogInstance($item);
        $this->save($transactionLog);

        $task->setTransactionLog($transactionLog);
    }

    /**
     * Saves transaction log.
     *
     * @param TransactionLog $transactionLog
     *
     * @return void
     *
     */
    public function save(TransactionLog $transactionLog): void
    {
        $this->transactionLogRepository->setTransactionLog($transactionLog);
    }

    /**
     * Saves transaction log.
     *
     * @param TransactionLog $transactionLog
     *
     * @return void
     *
     */
    public function update(TransactionLog $transactionLog): void
    {
        $this->transactionLogRepository->updateTransactionLog($transactionLog);
    }

    /**
     * Loads transaction log.
     *
     * @param QueueItem $item
     *
     * @return void
     *
     * @throws QueueItemDeserializationException
     */
    public function load(QueueItem $item): void
    {
        /** @var TransactionLogAware $task */
        $task = $item->getTask();

        if ($task === null) {
            return;
        }

        $id = $item->getParentId() ?? $item->getId();
        $log = $this->transactionLogRepository->getItemByExecutionId($id);
        if ($log) {
            $task->setTransactionLog($log);
        }
    }

    /**
     * @param QueueItem $queueItem
     *
     * @return bool
     */
    public function hasQueueItem(QueueItem $queueItem): bool
    {
        return !($this->transactionLogRepository->getItemByExecutionId($queueItem->getId()) === null);
    }

    /**
     * @param int $limit
     * @param int $offset
     *
     * @return TransactionLog[]
     *
     * @throws QueryFilterInvalidParamException
     * @throws Exception
     */
    public function find(int $limit, int $offset): array
    {
        $disconnectTime = $this->disconnectRepository->getDisconnectTime();

        return $this->transactionLogRepository->find($limit, $offset, $disconnectTime);
    }

    /**
     * @param string $merchantReference
     *
     * @return TransactionLog|null
     *
     * @throws Exception
     */
    public function findByMerchantReference(string $merchantReference): ?TransactionLog
    {
        return $this->transactionLogRepository->findByMerchantReference($merchantReference);
    }

    /**
     * @param int $page
     * @param int $limit
     *
     * @return bool
     *
     * @throws Exception
     */
    public function hasNextPage(int $page, int $limit): bool
    {
        $disconnectTime = $this->disconnectRepository->getDisconnectTime();
        $count = $this->transactionLogRepository->count($disconnectTime);

        if ($page <= 1) {
            return $limit < $count;
        }

        return $page * $limit < $count;
    }

    /**
     * @param DateTime $beforeDate
     * @param int $limit
     *
     * @return void
     *
     * @throws Exception
     */
    public function deleteLogs(DateTime $beforeDate, int $limit): void
    {
        $this->transactionLogRepository->deleteLogs($beforeDate, $limit);
    }

    /**
     * @param DateTime $beforeDate
     *
     * @return bool
     *
     * @throws Exception
     */
    public function logsExist(DateTime $beforeDate): bool
    {
        return $this->transactionLogRepository->logsExist($beforeDate);
    }

    /**
     * @param QueueItem $item
     * @return TransactionLog
     * @throws InvalidMerchantReferenceException
     * @throws QueueItemDeserializationException
     */
    private function createTransactionLogInstance(QueueItem $item): TransactionLog
    {
        /** @var TransactionLogAware $task */
        $task = $item->getTask();
        $eventDate = DateTime::createFromFormat(DateTimeInterface::ATOM, $task->getWebhook()->getEventDate());

        $transactionLog = new TransactionLog();
        $transactionLog->setStoreId($task->getStoreId() ?? '');
        $transactionLog->setMerchantReference($task->getWebhook()->getMerchantReference());
        $transactionLog->setExecutionId($item->getId() ?? 0);
        $transactionLog->setEventCode($task->getWebhook()->getEventCode());
        $transactionLog->setReason($task->getWebhook()->getReason());
        $transactionLog->setIsSuccessful($task->getWebhook()->isSuccess());
        $transactionLog->setTimestamp($eventDate->getTimestamp());
        $transactionLog->setPaymentMethod($task->getWebhook()->getPaymentMethod());
        $transactionLog->setAdyenLink(
            $this->transactionHistoryService->getTransactionHistory(
                $task->getWebhook()->getMerchantReference()
            )->getAdyenPaymentLinkFor($task->getWebhook()->getPspReference())
        );
        $transactionLog->setShopLink(
            $this->orderService->getOrderUrl($task->getWebhook()->getMerchantReference())
        );
        $transactionLog->setQueueStatus(QueueItem::QUEUED);
        $transactionLog->setPspReference($task->getWebhook()->getPspReference());

        return $transactionLog;
    }
}
