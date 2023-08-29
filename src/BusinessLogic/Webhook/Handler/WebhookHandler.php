<?php

namespace Adyen\Core\BusinessLogic\Webhook\Handler;

use Adyen\Core\BusinessLogic\Domain\Webhook\Models\Webhook;
use Adyen\Core\BusinessLogic\Domain\Webhook\Services\WebhookSynchronizationService;
use Adyen\Core\BusinessLogic\Webhook\Tasks\OrderUpdateTask;
use Adyen\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;
use Adyen\Core\Infrastructure\TaskExecution\QueueService;

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
     * @param WebhookSynchronizationService $synchronizationService
     * @param QueueService $queueService
     */
    public function __construct(WebhookSynchronizationService $synchronizationService, QueueService $queueService)
    {
        $this->synchronizationService = $synchronizationService;
        $this->queueService = $queueService;
    }

    /**
     * @param Webhook $webhook
     *
     * @return void
     *
     * @throws QueueStorageUnavailableException
     */
    public function handle(Webhook $webhook): void
    {
        if ($this->synchronizationService->isSynchronizationNeeded($webhook)) {
            $this->queueService->enqueue('OrderUpdate', new OrderUpdateTask($webhook));
        }
    }
}
