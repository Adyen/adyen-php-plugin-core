<?php

namespace Adyen\Core\Tests\BusinessLogic\AdminAPI\WebhookNotifications\MockComponents;

use Adyen\Core\BusinessLogic\DataAccess\TransactionLog\Entities\TransactionLog;
use Adyen\Core\BusinessLogic\TransactionLog\Services\TransactionLogService;

/**
 * Class MockTransactionLogService
 *
 * @package Adyen\Core\Tests\BusinessLogic\AdminAPI\WebhookNotifications\MockComponents
 */
class MockTransactionLogService extends TransactionLogService
{
    /**
     * @var TransactionLog[]
     */
    private $logs = [];

    /**
     * @param array $logs
     * @return void
     */
    public function setMockLogs(array $logs): void
    {
        $this->logs = $logs;
    }

    /**
     * @param int $limit
     * @param int $offset
     *
     * @return TransactionLog[]
     */
    public function find(int $limit, int $offset): array
    {
        return $this->logs;
    }
}
