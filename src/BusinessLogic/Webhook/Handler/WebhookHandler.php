<?php

namespace Adyen\Core\BusinessLogic\Webhook\Handler;

use Adyen\Core\BusinessLogic\DataAccess\TransactionLog\Entities\TransactionLog;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Exceptions\InvalidMerchantReferenceException;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models\HistoryItem;
use Adyen\Core\BusinessLogic\Domain\Webhook\Models\Webhook;
use Adyen\Core\BusinessLogic\Domain\Webhook\Services\WebhookSynchronizationService;
use Adyen\Core\BusinessLogic\TransactionLog\Services\TransactionLogService;
use Adyen\Core\BusinessLogic\Webhook\Tasks\OrderUpdateTask;
use Adyen\Core\BusinessLogic\WebhookAPI\Exceptions\WebhookShouldRetryException;
use Adyen\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use Adyen\Core\Infrastructure\ServiceRegister;
use Adyen\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;
use Adyen\Core\Infrastructure\TaskExecution\QueueItem;
use Adyen\Core\Infrastructure\TaskExecution\QueueService;
use Adyen\Core\Infrastructure\Utility\TimeProvider;

/**
 * Class WebhookHandler
 *
 * @package Adyen\Core\BusinessLogic\Webhook\Handler
 */
class WebhookHandler
{
    /**
     * @var WebhookSynchronizationService
     */
    private $synchronizationService;

    /**
     * @var QueueService
     */
    private $queueService;

    /**
     * @var TimeProvider
     */
    protected $timeProvider;

    /**
     * @param WebhookSynchronizationService $synchronizationService
     * @param QueueService $queueService
     * @param TimeProvider $timeProvider
     */
    public function __construct(
        WebhookSynchronizationService $synchronizationService,
        QueueService                  $queueService,
        TimeProvider                  $timeProvider)
    {
        $this->synchronizationService = $synchronizationService;
        $this->queueService = $queueService;
        $this->timeProvider = $timeProvider;
    }

    /**
     * @param Webhook $webhook
     *
     * @return void
     *
     * @throws QueueStorageUnavailableException
     * @throws InvalidMerchantReferenceException
     * @throws QueryFilterInvalidParamException
     */
    public function handle(Webhook $webhook): void
    {
        $synchronousProcessing = $this->synchronizationService->isExecuteOrderUpdateSynchronously();

        if (!$synchronousProcessing && $this->synchronizationService->isSynchronizationNeeded($webhook)) {
            $this->queueService->enqueue('OrderUpdate', new OrderUpdateTask($webhook));
            return;
        }

        /** @var TransactionLogService $transactionLogService */
        $transactionLogService = ServiceRegister::getService(TransactionLogService::class);

        if ($this->synchronizationService->exceededRetryLimit($webhook)) {
            return;
        }

        $this->synchronizationService->incrementRetryCount($webhook);

        $transactionLogId = $this->synchronizationService->getTransactionLogId($webhook);

        /** @var TransactionLog $transactionLog */
        $transactionLog = $transactionLogService->createSyncTransactionLogInstance($webhook, $transactionLogId);

        $item = $this->synchronizationService->fetchHistoryItem($webhook);

        $message = $this->shouldAbortExecution($transactionLog, $item);

        if (!empty($message)) {
            throw new WebhookShouldRetryException($message);
        }

        try {
            $this->synchronizationService->setStartedAtTimestamp($webhook);
            $transactionLog->setQueueStatus(QueueItem::IN_PROGRESS);
            $transactionLogService->update($transactionLog);

            $task = new OrderUpdateTask($webhook);
            $task->setTransactionLogId($transactionLog->getId());
            $task->execute();

            $transactionLog->setQueueStatus(QueueItem::COMPLETED);
            $transactionLog->setFailureDescription(null);
            $transactionLogService->update($transactionLog);
        } catch (\Throwable $exception) {
            $transactionLog->setQueueStatus(QueueItem::FAILED);
            $transactionLog->setFailureDescription($exception->getMessage());
            $transactionLogService->update($transactionLog);
        }
    }

    /**
     * Determines whether webhook should be processed again due to timeout in previous attempt.
     *
     * @param TransactionLog $log
     * @param HistoryItem|null $item
     *
     * @return string
     *
     */
    private function shouldAbortExecution(TransactionLog $log, ?HistoryItem $item = null): ?string
    {
        if ($item === null || $item->getStartedAt() === null) {
            return null;
        }

        if ($log->getQueueStatus() === QueueItem::FAILED) {
            return null;
        }

        if ($log->getQueueStatus() === QueueItem::COMPLETED) {
            return "Task already executed successfully!";
        }

        $elapsed = $this->timeProvider->getCurrentLocalTime()->getTimestamp() - $item->getStartedAt();

        if ($elapsed <= 10) {
            return "Task in progress, skipping!";
        }

        return null;
    }
}
