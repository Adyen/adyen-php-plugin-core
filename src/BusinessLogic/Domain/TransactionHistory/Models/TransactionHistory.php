<?php

namespace Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\CurrencyMismatchException;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Currency;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Models\CaptureType;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Exceptions\InvalidMerchantReferenceException;
use Adyen\Core\Infrastructure\Utility\TimeProvider;

/**
 * Class TransactionHistory
 *
 * @package Adyen\Core\BusinessLogic\Domain\TransactionHistoryHistoryHistory\Models
 */
class TransactionHistory
{
    /**
     * @var string
     */
    private $originalPspReference = '';

    /**
     * @var string
     */
    private $merchantReference = '';

    /**
     * @var int
     */
    private $riskScore = 0;

    /**
     * @var string
     */
    private $paymentMethod = '';

    /**
     * @var bool
     */
    private $isLive = false;

    /**
     * @var HistoryItemCollection
     */
    private $historyItemCollection;

    /**
     * @var CaptureType
     */
    private $captureType;

    /**
     * @var int Represented in number of hours.
     */
    private $captureDelay;

    /**
     * @var Currency
     */
    private $currency;

    /**
     * @param string $merchantReference
     * @param CaptureType $captureType
     * @param int $captureDelay
     * @param Currency|null $currency
     * @param array $historyItems
     *
     * @throws InvalidMerchantReferenceException
     */
    public function __construct(
        string $merchantReference,
        CaptureType $captureType,
        int $captureDelay = 0,
        Currency $currency = null,
        array $historyItems = []
    ) {
        $this->merchantReference = $merchantReference;
        $this->captureType = $captureType;
        $this->captureDelay = $captureDelay;
        $this->currency = $currency;
        $this->historyItemCollection = new HistoryItemCollection();

        foreach ($historyItems as $item) {
            $this->add($item);
        }
    }

    /**
     * @param HistoryItem $item
     *
     * @return void
     *
     * @throws InvalidMerchantReferenceException
     */
    public function add(HistoryItem $item): void
    {
        if (!$this->historyItemCollection->filterByPspReference($item->getPspReference())->isEmpty()) {
            return;
        }

        if ($item->getMerchantReference() !== $this->merchantReference) {
            throw new InvalidMerchantReferenceException(
                sprintf(
                    'History item with wrong merchant reference. Tried to add history item with merchant reference %s to transaction history for %s merchant reference.',
                    $item->getMerchantReference(),
                    $this->merchantReference
                )
            );
        }

        if ($this->historyItemCollection->isEmpty()) {
            $this->originalPspReference = $item->getPspReference();
            $this->merchantReference = $item->getMerchantReference();
            $this->paymentMethod = $item->getPaymentMethod();
            $this->isLive = $item->isLive();
            $this->riskScore = $item->getRiskScore();
        }

        if ($item->getRiskScore() > 0 && $this->riskScore !== $item->getRiskScore()) {
            $this->riskScore = $item->getRiskScore();
        }

        $this->historyItemCollection->add($item);
    }

    /**
     * @param string $eventCode
     *
     * @return Amount
     *
     * @throws CurrencyMismatchException
     */
    public function getTotalAmountForEventCode(string $eventCode): Amount
    {
        return $this->historyItemCollection->filterByEventCode($eventCode)->filterByStatus(true)->getTotalAmount($this->currency);
    }

    /**
     * @return Amount|null
     *
     * @throws CurrencyMismatchException
     */
    public function getCapturedAmount(): ?Amount
    {
        if ($this->captureType->equal(CaptureType::manual())) {
            return $this->getTotalAmountForEventCode('CAPTURE');
        }

        if ($this->captureType->equal(CaptureType::immediate()) || $this->captureType->equal(CaptureType::unknown())) {
            return $this->getTotalAmountForEventCode('AUTHORISATION');
        }

        $authorisedItem = $this->collection()->filterByEventCode('AUTHORISATION')->first();

        if (!$authorisedItem) {
            return Amount::fromInt(0, $this->currency);
        }

        $authorisedDate = TimeProvider::getInstance()->deserializeDateString($authorisedItem->getDateAndTime())->getTimestamp();

        if ($authorisedDate + $this->captureDelay * 3600 < TimeProvider::getInstance()->getCurrentLocalTime()->getTimestamp()) {
            return $this->getTotalAmountForEventCode('AUTHORISATION');
        }

        return $this->getTotalAmountForEventCode('CAPTURE');
    }

    /**
     * @param string $pspReference PSP reference that belongs to this transaction history based on merchant order reference
     *
     * @return string
     */
    public function getAdyenPaymentLinkFor(string $pspReference): string
    {
        if (!$this->collection()->isEmpty()) {
            $pspReference = $this->getOriginalPspReference();
        }

        $url = 'https://ca-';
        $url .= $this->isLive() ? 'live' : 'test';
        $url .= '.adyen.com/ca/ca/accounts/showTx.shtml?pspReference=';
        $url .= $pspReference;
        $url .= '&txType=Payment';

        return $url;
    }

    /**
     * @return string
     */
    public function getOriginalPspReference(): string
    {
        return $this->originalPspReference;
    }

    /**
     * @return string
     */
    public function getMerchantReference(): string
    {
        return $this->merchantReference;
    }

    /**
     * @return string
     */
    public function getPaymentMethod(): string
    {
        return $this->paymentMethod;
    }

    /**
     * @return int
     */
    public function getRiskScore(): int
    {
        return $this->riskScore;
    }

    /**
     * @return HistoryItemCollection
     */
    public function collection(): HistoryItemCollection
    {
        return $this->historyItemCollection;
    }

    /**
     * @return ?bool
     */
    public function isLive(): ?bool
    {
        return $this->isLive;
    }

    /**
     * @return CaptureType
     */
    public function getCaptureType(): CaptureType
    {
        return $this->captureType;
    }

    /**
     * @return int
     */
    public function getCaptureDelay(): int
    {
        return $this->captureDelay;
    }

    /**
     * @return Currency|null
     */
    public function getCurrency(): ?Currency
    {
        return $this->currency;
    }
}
