<?php

namespace Adyen\Core\BusinessLogic\Domain\TransactionHistory\Repositories;

use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory;
use Exception;

/**
 * Interface TransactionHistoryRepository
 *
 * @package Adyen\Core\BusinessLogic\Domain\TransactionHistoryHistory\Repositories
 */
interface TransactionHistoryRepository
{
    /**
     * Returns TransactionHistory instance for current store context and with Merchant Reference provided as parameter.
     *
     * @param string $merchantReference
     *
     * @return TransactionHistory|null
     */
    public function getTransactionHistory(string $merchantReference): ?TransactionHistory;

    /**
     * Insert/update TransactionHistory for current store context;
     *
     * @param TransactionHistory $transaction
     *
     * @return void
     */
    public function setTransactionHistory(TransactionHistory $transaction): void;

    /**
     * @param array $merchantReferences
     *
     * @return array
     *
     * @throws Exception
     */
    public function getTransactionHistoriesByMerchantReferences(array $merchantReferences): array;
}
