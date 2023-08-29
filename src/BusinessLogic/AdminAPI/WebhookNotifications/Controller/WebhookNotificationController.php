<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\WebhookNotifications\Controller;

use Adyen\Core\BusinessLogic\AdminAPI\WebhookNotifications\Response\WebhookNotificationResponse;
use Adyen\Core\BusinessLogic\TransactionLog\Services\TransactionLogService;
use Adyen\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use Exception;

/**
 * Class WebhookNotificationController
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\WebhookNotifications\Controller
 */
class WebhookNotificationController
{
    /**
     * @var TransactionLogService
     */
    private $transactionLogService;

    /**
     * @param TransactionLogService $transactionLogService
     */
    public function __construct(TransactionLogService $transactionLogService)
    {
        $this->transactionLogService = $transactionLogService;
    }

    /**
     * @param int $page
     * @param int $limit
     *
     * @return WebhookNotificationResponse
     *
     * @throws QueryFilterInvalidParamException
     *
     * @throws Exception
     */
    public function getNotifications(int $page, int $limit): WebhookNotificationResponse
    {
        $logs = $this->transactionLogService->find($limit, ($page - 1) * $limit);
        $hasNextPage = $this->transactionLogService->hasNextPage($page, $limit);

        return new WebhookNotificationResponse($hasNextPage, $logs);
    }
}
