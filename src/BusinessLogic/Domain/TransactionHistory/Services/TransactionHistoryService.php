<?php

namespace Adyen\Core\BusinessLogic\Domain\TransactionHistory\Services;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Currency;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Models\CaptureType;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Repositories\GeneralSettingsRepository;
use Adyen\Core\BusinessLogic\Domain\PartialPayments\Models\Order;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\AuthorizationType;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Exceptions\InvalidMerchantReferenceException;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models\HistoryItem;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Repositories\TransactionHistoryRepository;

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
     * @param AuthorizationType|null $authorizationType
     *
     * @return TransactionHistory
     *
     * @throws InvalidMerchantReferenceException
     */
    public function getTransactionHistory(
        string $merchantReference,
        Currency $currency = null,
        CaptureType $captureType = null,
        AuthorizationType $authorizationType = null,
        HistoryItem $historyItem = null,
        Order $order = null
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
                $currency ?? Currency::getDefault(),
                $authorizationType,
                $historyItem ? [$historyItem] : []
            );
        }

        if ($historyItem) {
            $transactionHistory->add($historyItem);
            $transactionHistory->setAuthorizationPspReferences(
                array_merge(
                    $transactionHistory->getAuthorizationPspReferences(),
                    [$historyItem->getAuthorizationPspReference()]
                )
            );
        }

        if ($order) {
            $transactionHistory->setOrderData($order->getOrderData());
            $transactionHistory->setOrderPspReference($order->getPspReference());
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
     * @param string $merchantReference
     * @param Currency $currency
     * @param CaptureType|null $captureType
     * @param AuthorizationType|null $authorizationType
     *
     * @return void
     *
     * @throws InvalidMerchantReferenceException
     */
    public function createTransactionHistory(
        string $merchantReference,
        Currency $currency,
        CaptureType $captureType = null,
        AuthorizationType $authorizationType = null,
        HistoryItem $historyItem = null,
        Order $order = null
    ): void {
        $history = $this->getTransactionHistory($merchantReference, $currency, $captureType, $authorizationType, $historyItem, $order);

        $this->setTransactionHistory($history);
    }
}
