<?php

namespace Adyen\Core\BusinessLogic\TransactionLog\Repositories;

use Adyen\Core\BusinessLogic\DataAccess\TransactionLog\Entities\TransactionLog;
use Adyen\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use DateTime;
use Exception;

/**
 * Interface TransactionLogRepository
 *
 * @package Adyen\Core\BusinessLogic\Domain\WebhookNotifications\Repositories
 */
interface TransactionLogRepository
{
    /**
     * Returns TransactionLog instance for current store context.
     *
     * @param string $merchantReference
     *
     * @return TransactionLog|null
     */
    public function getTransactionLog(string $merchantReference): ?TransactionLog;

    /**
     * Insert TransactionLog for current store context;
     *
     * @param TransactionLog $transactionLog
     *
     * @return void
     */
    public function setTransactionLog(TransactionLog $transactionLog): void;

    /**
     * Insert TransactionLog for current store context;
     *
     * @param TransactionLog $transactionLog
     *
     * @return void
     */
    public function updateTransactionLog(TransactionLog $transactionLog): void;

    /**
     * @param int $executionId
     *
     * @return ?TransactionLog
     */
    public function getItemByExecutionId(int $executionId): ?TransactionLog;

    /**
     * @param int $limit
     * @param int $offset
     * @param DateTime|null $disconnectTime
     *
     * @return TransactionLog[]
     *
     * @throws QueryFilterInvalidParamException
     */
    public function find(int $limit, int $offset, ?DateTime $disconnectTime = null): array;

    /**
     * @param DateTime|null $disconnectTime
     *
     * @return int
     */
    public function count(?DateTime $disconnectTime = null): int;

    /**
     * @param string $merchantReference
     *
     * @return TransactionLog|null
     *
     * @throws Exception
     */
    public function findByMerchantReference(string $merchantReference): ?TransactionLog;

    /**
     * Checks if there are logs before given date.
     *
     * @param DateTime $beforeDate
     *
     * @return bool
     *
     * @throws Exception
     */
    public function logsExist(DateTime $beforeDate): bool;

    /**
     * Deletes logs before given date.
     *
     * @param DateTime $beforeDate
     * @param int $limit
     *
     * @return void
     *
     * @throws Exception
     */
    public function deleteLogs(DateTime $beforeDate, int $limit): void;

    /**
     * Find transaction log by its ID.
     *
     * @param int $id
     *
     * @return TransactionLog|null
     *
     * @throws QueryFilterInvalidParamException
     */
    public function findById(int $id): ?TransactionLog;
}
