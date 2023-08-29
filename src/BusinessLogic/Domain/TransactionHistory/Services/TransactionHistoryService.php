<?php

namespace Adyen\Core\BusinessLogic\Domain\TransactionHistory\Services;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Currency;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Models\CaptureType;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Repositories\GeneralSettingsRepository;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Exceptions\InvalidMerchantReferenceException;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Repositories\TransactionHistoryRepository;
use Exception;

/**
 * Interface TransactionHistoryService
 *
 * @package Adyen\Core\BusinessLogic\Domain\TransactionHistoryHistory\Repositories
 */
class TransactionHistoryService
{
    /**
     * @var TransactionHistoryRepository
     */
    private $transactionRepository;

    /**
     * @var GeneralSettingsRepository
     */
    private $generalSettingsRepository;

    /**
     * @param TransactionHistoryRepository $transactionRepository
     * @param GeneralSettingsRepository $generalSettingsRepository
     */
    public function __construct(
        TransactionHistoryRepository $transactionRepository,
        GeneralSettingsRepository $generalSettingsRepository
    ) {
        $this->transactionRepository = $transactionRepository;
        $this->generalSettingsRepository = $generalSettingsRepository;
    }

    /**
     * @param string $merchantReference
     * @param Currency|null $currency
     * @param CaptureType|null $captureType
     *
     * @return TransactionHistory
     *
     * @throws InvalidMerchantReferenceException
     */
    public function getTransactionHistory(
        string $merchantReference,
        Currency $currency = null,
        CaptureType $captureType = null
    ): TransactionHistory {
        $transactionHistory = $this->transactionRepository->getTransactionHistory($merchantReference);

        $captureDelayHours = 0;
        if (!$transactionHistory && !$captureType) {
            $generalSettings = $this->generalSettingsRepository->getGeneralSettings();
            $captureType = $generalSettings ? $generalSettings->getCapture() : CaptureType::immediate();
            $captureDelayHours = $generalSettings ? $generalSettings->getCaptureDelayHours() : 0;
        }

        if (!$transactionHistory) {
            $transactionHistory = new TransactionHistory(
                $merchantReference,
                $captureType,
                $captureDelayHours,
                $currency ?? Currency::getDefault()
            );
        }

        return $transactionHistory;
    }

    /**
     * @param TransactionHistory $transaction
     *
     * @return void
     */
    public function setTransactionHistory(TransactionHistory $transaction): void
    {
        $this->transactionRepository->setTransactionHistory($transaction);
    }

    /**
     * @throws InvalidMerchantReferenceException
     */
    public function createTransactionHistory(
        string $merchantReference,
        Currency $currency,
        CaptureType $type = null
    ): void {
        $history = $this->getTransactionHistory($merchantReference, $currency, $type);

        $this->setTransactionHistory($history);
    }

    /**
     * @param array $merchantReferences
     *
     * @return TransactionHistory[]
     *
     * @throws Exception
     */
    public function getTransactionHistoriesByReferences(array $merchantReferences): array
    {
        return $this->transactionRepository->getTransactionHistoriesByMerchantReferences($merchantReferences);
    }
}
