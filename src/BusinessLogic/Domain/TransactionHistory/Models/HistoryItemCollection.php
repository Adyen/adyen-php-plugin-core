<?php

namespace Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\CurrencyMismatchException;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Currency;

/**
 * Class HistoryItemCollection
 *
 * @package Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models
 */
class HistoryItemCollection
{
    /**
     * @var HistoryItem[]
     */
    private $historyItems;

    /**
     * @param HistoryItem[] $historyItems
     */
    public function __construct(array $historyItems = [])
    {
        $this->historyItems = $historyItems;
    }

    /**
     * @return HistoryItem[]
     */
    public function getAll(): array
    {
        return $this->historyItems;
    }

    /**
     * Adds history item to collection.
     *
     * @param HistoryItem $item
     *
     * @return void
     */
    public function add(HistoryItem $item): void
    {
        $this->historyItems[] = $item;
    }

    /**
     * @param string $pspReference
     *
     * @return $this
     */
    public function filterByPspReference(string $pspReference): self
    {
        return new self  (
            array_filter($this->historyItems, static function ($item) use ($pspReference) {
                return $item->getPspReference() === $pspReference;
            })
        );
    }

    /**
     * @param string $eventCode
     *
     * @return $this
     */
    public function filterByEventCode(string $eventCode): self
    {
        return new self  (
            array_filter($this->historyItems, static function ($item) use ($eventCode) {
                return $item->getEventCode() === $eventCode;
            })
        );
    }

    /**
     * @param bool $status
     *
     * @return $this
     */
    public function filterByStatus(bool $status): self
    {
        return new self  (
            array_filter($this->historyItems, static function ($item) use ($status) {
                return $item->getStatus() === $status;
            })
        );
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->historyItems);
    }

    /**
     * @return HistoryItem|null
     */
    public function last(): ?HistoryItem
    {
        return !$this->isEmpty() ? end($this->historyItems) : null;
    }

    /**
     * @return HistoryItem|null
     */
    public function first(): ?HistoryItem
    {
        return !$this->isEmpty() ? current($this->historyItems) : null;
    }

    /**
     * @param Currency $currency
     *
     * @return Amount
     *
     * @throws CurrencyMismatchException
     */
    public function getTotalAmount(Currency $currency): Amount
    {
        if ($this->isEmpty()) {
            return Amount::fromInt(0, $currency);
        }

        return array_reduce($this->historyItems, static function (?Amount $totalAmount, HistoryItem $item) {
            return $totalAmount ? $totalAmount->plus($item->getAmount()) : $item->getAmount();
        });
    }
}
