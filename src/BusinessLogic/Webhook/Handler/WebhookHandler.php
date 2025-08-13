<?php

namespace Adyen\Core\BusinessLogic\Webhook\Handler;

use Adyen\Core\BusinessLogic\DataAccess\TransactionLog\Entities\TransactionLog;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Exceptions\InvalidMerchantReferenceException;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models\HistoryItem;
use Adyen\Core\BusinessLogic\Domain\Webhook\Models\Webhook;
use Adyen\Core\BusinessLogic\Domain\Webhook\Services\WebhookSynchronizationService;
use Adyen\Core\BusinessLogic\TransactionLog\Services\TransactionLogService;
use Adyen\Core\BusinessLogic\Webhook\Tasks\OrderUpdateTask;
use Adyen\Core\BusinessLogic\Webhook\Tasks\SynchronousOrderUpdateTask;
use Adyen\Core\BusinessLogic\WebhookAPI\Exceptions\WebhookShouldRetryException;
use Adyen\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use Adyen\Core\Infrastructure\ServiceRegister;
use Adyen\Core\Infrastructure\TaskExecution\Exceptions\AbortTaskExecutionException;
use Adyen\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;
use Adyen\Core\Infrastructure\TaskExecution\Interfaces\TaskRunnerWakeup;
use Adyen\Core\Infrastructure\TaskExecution\QueueItem;
use Adyen\Core\Infrastructure\TaskExecution\QueueService;
use Adyen\Core\Infrastructure\Utility\TimeProvider;
use Throwable;

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
     * Task runner wakeup instance.
     *
     * @var TaskRunnerWakeup
     */
    private $taskRunnerWakeup;

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
        QueueService $queueService,
        TimeProvider $timeProvider
    ) {
        $this->synchronizationService = $synchronizationService;
        $this->queueService = $queueService;
        $this->timeProvider = $timeProvider;
    }

    /**
     * @param Webhook $webhook
     *
     * @return void
     *
     * @throws InvalidMerchantReferenceException
     * @throws QueryFilterInvalidParamException
     * @throws QueueStorageUnavailableException
     * @throws WebhookShouldRetryException
     * @throws AbortTaskExecutionException
     * @throws Throwable
     */
    public function handle(Webhook $webhook): void
    {
        if (!$this->synchronizationService->isSynchronizationNeeded($webhook)) {
            return;
        }

        $synchronousProcessing = $this->synchronizationService->isExecuteOrderUpdateSynchronously();

        if (!$synchronousProcessing) {
            $this->asynchronousProcessing($webhook);

            return;
        }

        $this->getTaskRunnerWakeup()->wakeup();

        /** @var TransactionLogService $transactionLogService */
        $transactionLogService = ServiceRegister::getService(TransactionLogService::class);

        if ($this->synchronizationService->exceededRetryLimit($webhook)) {
            return;
        }

        $this->synchronizationService->incrementRetryCount($webhook);

        $transactionLogId = $this->synchronizationService->getTransactionLogId($webhook);

        /** @var TransactionLog $transactionLog */
        $transactionLog = $transactionLogService->createSyncTransactionLogInstance($webhook, $transactionLogId);
        if (in_array($transactionLog->getQueueStatus(), [QueueItem::COMPLETED, QueueItem::ABORTED])) {
            return;
        }

        $item = $this->synchronizationService->fetchHistoryItem($webhook);
        $message = $this->shouldAbortExecution($transactionLog, $item);

        if (!empty($message)) {
            throw new WebhookShouldRetryException($message);
        }

        try {
            $this->synchronizationService->setStartedAtTimestamp($webhook);
            $transactionLog->setQueueStatus(QueueItem::IN_PROGRESS);
            $transactionLogService->update($transactionLog);

            $task = new SynchronousOrderUpdateTask($webhook);
            $task->setTransactionLogId($transactionLog->getId());
            $task->execute();

            $transactionLog->setQueueStatus(QueueItem::COMPLETED);
            $transactionLog->setFailureDescription(null);
            $transactionLogService->update($transactionLog);
        } catch (Throwable $exception) {
            $transactionLog->setQueueStatus(QueueItem::FAILED);
            $transactionLog->setFailureDescription($exception->getMessage());
            $transactionLogService->update($transactionLog);

            throw $exception;
        }
    }

    /**
     * @param Webhook $webhook
     *
     * @return void
     *
     * @throws QueueStorageUnavailableException
     */
    private function asynchronousProcessing(Webhook $webhook): void
    {
        $this->queueService->enqueue('OrderUpdate', new OrderUpdateTask($webhook));
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

        $elapsed = $this->timeProvider->getCurrentLocalTime()->getTimestamp() - $item->getStartedAt();

        return ($elapsed <= 10) ? "Task in progress, skipping!" : null;
    }

    /**
     * Gets task runner wakeup instance.
     *
     * @return TaskRunnerWakeup Task runner wakeup instance.
     */
    private function getTaskRunnerWakeup(): TaskRunnerWakeup
    {
        if ($this->taskRunnerWakeup === null) {
            $this->taskRunnerWakeup = ServiceRegister::getService(TaskRunnerWakeup::CLASS_NAME);
        }

        return $this->taskRunnerWakeup;
    }
}
