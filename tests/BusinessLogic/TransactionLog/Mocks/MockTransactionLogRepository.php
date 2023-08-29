<?php

namespace Adyen\Core\Tests\BusinessLogic\TransactionLog\Mocks;

use Adyen\Core\BusinessLogic\DataAccess\TransactionLog\Entities\TransactionLog;
use Adyen\Core\BusinessLogic\TransactionLog\Repositories\TransactionLogRepository;
use DateTime;

/**
 * Class MockTransactionLogRepository
 *
 * @package Adyen\Core\Tests\BusinessLogic\TransactionLog\Mocks
 */
class MockTransactionLogRepository implements TransactionLogRepository
{
    /**
     * @var TransactionLog
     */
    private $transactionLog;

    /**
     * @inheritDoc
     */
    public function getTransactionLog(string $merchantReference): ?TransactionLog
    {
        return $this->transactionLog;
    }

    /**
     * @inheritDoc
     */
    public function setTransactionLog(TransactionLog $transactionLog): void
    {
        $this->transactionLog = $transactionLog;
    }

    /**
     * @inheritDoc
     */
    public function getItemByExecutionId(int $executionId): ?TransactionLog
    {
        return $this->transactionLog;
    }

    public function find(int $limit, int $offset, ?DateTime $disconnectTime = null): array
    {
        return [];
    }

    public function count(?DateTime $disconnectTime = null): int
    {
        return 1;
    }

    public function findByMerchantReference(string $merchantReference): ?TransactionLog
    {
        return null;
    }

    public function updateTransactionLog(TransactionLog $transactionLog): void
    {

    }

    public function logsExist(DateTime $beforeDate): bool
    {
        return false;
    }

    public function deleteLogs(DateTime $beforeDate, int $limit): void
    {
    }
}
