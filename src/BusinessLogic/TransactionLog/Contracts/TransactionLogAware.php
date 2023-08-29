<?php

namespace Adyen\Core\BusinessLogic\TransactionLog\Contracts;

use Adyen\Core\BusinessLogic\DataAccess\TransactionLog\Entities\TransactionLog;
use Adyen\Core\BusinessLogic\Domain\Webhook\Models\Webhook;

/**
 * Interface TransactionLogAware
 *
 * @package Adyen\Core\BusinessLogic\Domain\WebhookNotifications\Contracts
 */
interface TransactionLogAware
{
    /**
     * Provides transaction log.
     *
     * @return TransactionLog
     */
    public function getTransactionLog(): TransactionLog;

    /**
     * Sets transaction log.
     *
     * @param TransactionLog $transactionLog
     */
    public function setTransactionLog(TransactionLog $transactionLog);

    /**
     * Gets the store id for which the transactional task is created
     *
     * @return string
     */
    public function getStoreId(): string;

    /**
     * Gets the webhook instance for which the transactional task is created
     *
     * @return Webhook
     */
    public function getWebhook(): Webhook;
}
