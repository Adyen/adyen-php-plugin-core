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
            array_filter($this->filterAuthorisedItems(), static function ($item) use ($pspReference) {
                return $item->getPspReference() === $pspReference;
            })
        );
    }

    public function filterAllByPspReference(string $pspReference): self
    {
        return new self  (
            array_filter($this->getAll(), static function ($item) use ($pspReference) {
                return $item->getPspReference() === $pspReference;
            })
        );
    }

    /**
     * @param string $reference
     *
     * @return $this
     */
    public function filterByOriginalReference(string $reference): self
    {
        return new self (
            array_filter($this->getAll(), static function ($item) use ($reference) {
                return $item->getAuthorizationPspReference() === $reference;
            })
        );
    }

    public function filterAllByEventCode(string $eventCode): self
    {
        return new self  (
            array_filter($this->getAll(), static function ($item) use ($eventCode) {
                return $item->getEventCode() === $eventCode;
            })
        );
    }

    public function filterAllByStatus(bool $status): self
    {
        return new self  (
            array_filter($this->getAll(), static function ($item) use ($status) {
                return $item->getStatus() === $status;
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
            array_filter($this->filterAuthorisedItems(), static function ($item) use ($eventCode) {
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
            array_filter($this->filterAuthorisedItems(), static function ($item) use ($status) {
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
        return !$this->isEmpty() ? current($this->filterAuthorisedItems()) : null;
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

        return array_reduce($this->filterAuthorisedItems(), static function (?Amount $totalAmount, HistoryItem $item) {
            return $totalAmount ? $totalAmount->plus($item->getAmount()) : $item->getAmount();
        });
    }

    /**
     * @param Currency $currency
     *
     * @return Amount
     *
     * @throws CurrencyMismatchException
     */
    public function getAmount(Currency $currency): Amount
    {
        if ($this->isEmpty()) {
            return Amount::fromInt(0, $currency);
        }

        return array_reduce($this->historyItems, static function (?Amount $totalAmount, HistoryItem $item) {
            return $totalAmount ? $totalAmount->plus($item->getAmount()) : $item->getAmount();
        });
    }

    /**
     * Returns all elements after the one given as parameter.
     *
     * @param HistoryItem $historyItem
     *
     * @return HistoryItemCollection
     */
    public function trimFromHistoryItem(HistoryItem $historyItem): HistoryItemCollection
    {
        $found = false;
        $result = [];

        foreach ($this->historyItems as $object) {
            if (!$found && $object === $historyItem) {
                $found = true;
            }

            if ($found) {
                $result[] = $object;
            }
        }

        return new self($result);
    }


    /**
     * Find sub array in transaction history starting from last AUTHORISATION code.
     *
     * @return HistoryItem[]
     */
    private function filterAuthorisedItems(): array
    {
        $lastIndex = false;

        foreach (array_reverse($this->historyItems) as $index => $item) {
            if ($item->getEventCode() === 'AUTHORISATION' && $item->getStatus()) {
                $lastIndex = count($this->historyItems) - $index - 1;

                break;
            }
        }

        if (!$lastIndex) {
            return $this->historyItems;
        }

        return array_slice($this->historyItems, $lastIndex);
    }
}
