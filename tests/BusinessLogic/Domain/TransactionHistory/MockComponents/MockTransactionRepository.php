<?php

namespace Adyen\Core\Tests\BusinessLogic\Domain\TransactionHistory\MockComponents;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Currency;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Models\CaptureType;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Repositories\TransactionHistoryRepository;

/**
 * Class MockTransactionRepository
 *
 * @package Adyen\Core\Tests\BusinessLogic\Domain\TransactionHistoryHistory\MockComponents
 */
class MockTransactionRepository implements TransactionHistoryRepository
{
    /**
     * @var TransactionHistory
     */
    private $transaction;

    public function __construct()
    {
        $this->transaction = new TransactionHistory('merchantRef', CaptureType::manual(), 1, Currency::getDefault());
    }

    /**
     * @inheritDoc
     */
    public function getTransactionHistory(string $pspReference): ?TransactionHistory
    {
        return $this->transaction;
    }

    /**
     * @inheritDoc
     */
    public function setTransactionHistory(?TransactionHistory $transaction): void
    {
        $this->transaction = $transaction;
    }

    public function getTransactionHistoriesByMerchantReferences(array $merchantReferences): array
    {
        return [$this->transaction];
    }
}
