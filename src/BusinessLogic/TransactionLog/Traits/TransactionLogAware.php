<?php

namespace Adyen\Core\BusinessLogic\TransactionLog\Traits;

use Adyen\Core\BusinessLogic\DataAccess\TransactionLog\Entities\TransactionLog;

trait TransactionLogAware
{
    /**
     * @var TransactionLog
     */
    protected $transactionLog;

    /**
     * @return TransactionLog
     */
    public function getTransactionLog(): TransactionLog
    {
        return $this->transactionLog;
    }

    /**
     * @param TransactionLog $transactionLog
     */
    public function setTransactionLog(TransactionLog $transactionLog): void
    {
        $this->transactionLog = $transactionLog;
    }
}
