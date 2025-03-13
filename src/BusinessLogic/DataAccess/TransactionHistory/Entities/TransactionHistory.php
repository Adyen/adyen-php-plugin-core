<?php

namespace Adyen\Core\BusinessLogic\DataAccess\TransactionHistory\Entities;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Models\PaymentLink;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Models\PaymentLink as DomainPaymentLink;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\InvalidCurrencyCode;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Currency;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Exceptions\InvalidCaptureTypeException;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Models\CaptureType;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\AuthorizationType;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\Exceptions\InvalidAuthorizationTypeException;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Exceptions\InvalidMerchantReferenceException;
use Adyen\Core\Infrastructure\ORM\Configuration\EntityConfiguration;
use Adyen\Core\Infrastructure\ORM\Configuration\IndexMap;
use Adyen\Core\Infrastructure\ORM\Entity;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory as DomainTransactionHistory;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models\HistoryItem as DomainHistoryItem;

/**
 * Class TransactionHistory
 *
 * @package Adyen\Core\BusinessLogic\DataAccess\TransactionHistory\Entities
 */
class TransactionHistory extends Entity
{
    /**
     * Fully qualified name of this class.
     */
    public const CLASS_NAME = __CLASS__;

    /**
     * @var string
     */
    protected $storeId;

    /**
     * @var string
     */
    protected $merchantReference;

    /**
     * @var DomainTransactionHistory
     */
    protected $transactionHistory;

    /**
     * @inheritDoc
     */
    public function getConfig(): EntityConfiguration
    {
        $indexMap = new IndexMap();
        $indexMap->addStringIndex('storeId')
            ->addStringIndex('merchantReference');

        return new EntityConfiguration($indexMap, 'TransactionHistory');
    }

    /**
     * @inheritDoc
     *
     * @throws InvalidCurrencyCode
     * @throws InvalidMerchantReferenceException
     * @throws InvalidCaptureTypeException
     * @throws InvalidAuthorizationTypeException
     */
    public function inflate(array $data): void
    {
        parent::inflate($data);

        $transactionHistory = $data['transactionHistory'] ?? [];
        $this->storeId = $data['storeId'];
        $this->merchantReference = $data['merchantReference'];

        $this->transactionHistory = new DomainTransactionHistory(
            $this->merchantReference,
            CaptureType::fromState(self::getDataValue($transactionHistory, 'captureType')),
            self::getDataValue($transactionHistory, 'captureDelay'),
            Currency::fromIsoCode(self::getDataValue($transactionHistory, 'currency')),
            self::getDataValue($transactionHistory, 'authorizationType') ?
                AuthorizationType::fromState(self::getDataValue($transactionHistory, 'authorizationType')) : null,
            $this->historyItemCollectionFromArray(self::getDataValue($transactionHistory, 'historyItemCollection')),
            self::getDataValue($transactionHistory, 'orderData'),
            self::getDataValue($transactionHistory, 'orderPspReference'),
            self::getDataValue($transactionHistory, 'authorizationPspReferences', [])
        );

        $paymentLink = self::getDataValue($transactionHistory, 'paymentLink');
        if (!empty($paymentLink)) {
            $this->paymentLinkFromArray($paymentLink);
        }
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $data = parent::toArray();

        $data['storeId'] = $this->storeId;
        $data['merchantReference'] = $this->merchantReference;
        $data['transactionHistory'] = $this->transactionHistoryToArray();

        return $data;
    }

    /**
     * @return string
     */
    public function getStoreId(): string
    {
        return $this->storeId;
    }

    /**
     * @param string $storeId
     */
    public function setStoreId(string $storeId): void
    {
        $this->storeId = $storeId;
    }

    /**
     * @return DomainTransactionHistory
     */
    public function getTransactionHistory(): DomainTransactionHistory
    {
        return $this->transactionHistory;
    }

    /**
     * @param DomainTransactionHistory $transactionHistory
     */
    public function setTransactionHistory(DomainTransactionHistory $transactionHistory): void
    {
        $this->transactionHistory = $transactionHistory;
    }

    /**
     * @return string
     */
    public function getMerchantReference(): string
    {
        return $this->merchantReference;
    }

    /**
     * @param string $merchantReference
     */
    public function setMerchantReference(string $merchantReference): void
    {
        $this->merchantReference = $merchantReference;
    }

    /**
     * @return array
     */
    private function transactionHistoryToArray(): array
    {
        return [
            'originalPspReference' => $this->transactionHistory->getOriginalPspReference(),
            'merchantReference' => $this->transactionHistory->getMerchantReference(),
            'historyItemCollection' => $this->historyItemCollectionToArray(),
            'riskScore' => $this->transactionHistory->getRiskScore(),
            'paymentMethod' => $this->transactionHistory->getPaymentMethod(),
            'isLive' => $this->transactionHistory->isLive(),
            'captureType' => $this->transactionHistory->getCaptureType()->getType(),
            'captureDelay' => $this->transactionHistory->getCaptureDelay(),
            'currency' => $this->transactionHistory->getCurrency() ? $this->transactionHistory->getCurrency()->getIsoCode() : Currency::getDefault()->getIsoCode(),
            'paymentLink' => $this->paymentLinkToArray($this->transactionHistory->getPaymentLink()),
            'authorizationType' => $this->transactionHistory->getAuthorizationType() ? $this->transactionHistory->getAuthorizationType()->getType() : null,
            'orderData' => $this->transactionHistory->getOrderData(),
            'orderPspReference' => $this->transactionHistory->getOrderPspReference(),
            'authorizationPspReferences' => $this->transactionHistory->getAuthorizationPspReferences()
        ];
    }

    /**
     * @return array
     */
    private function historyItemCollectionToArray(): array
    {
        $historyItemCollection = [];

        foreach ($this->transactionHistory->collection()->getAll() as $transactionHistoryItem) {
            $historyItemCollection[] = [
                'pspReference' => $transactionHistoryItem->getPspReference(),
                'merchantReference' => $transactionHistoryItem->getMerchantReference(),
                'eventCode' => $transactionHistoryItem->getEventCode(),
                'paymentState' => $transactionHistoryItem->getPaymentState(),
                'dateAndTime' => $transactionHistoryItem->getDateAndTime(),
                'status' => $transactionHistoryItem->getStatus(),
                'amount' => $this->amountToArray($transactionHistoryItem->getAmount()),
                'paymentMethod' => $transactionHistoryItem->getPaymentMethod(),
                'riskScore' => $transactionHistoryItem->getRiskScore(),
                'isLive' => $transactionHistoryItem->isLive(),
                'authorizationPspReference' => $transactionHistoryItem->getAuthorizationPspReference(),
                'captureType' => $transactionHistoryItem->getCaptureType() ?
                    $transactionHistoryItem->getCaptureType()->getType() : '',
            ];
        }

        return $historyItemCollection;
    }

    /**
     * @param array $transactionHistoryArray
     *
     * @return array
     *
     * @throws InvalidCurrencyCode
     * @throws InvalidCaptureTypeException
     */
    private function historyItemCollectionFromArray(array $transactionHistoryArray): array
    {
        $transactionHistoryItems = [];

        foreach ($transactionHistoryArray as $value) {
            $transactionHistoryItems[] = new DomainHistoryItem(
                $value['pspReference'],
                $value['merchantReference'],
                $value['eventCode'],
                $value['paymentState'],
                $value['dateAndTime'],
                $value['status'],
                $this->amountFromArray($value['amount']),
                $value['paymentMethod'],
                $value['riskScore'],
                $value['isLive'],
                static::getDataValue($value, 'authorizationPspReference'),
                static::getDataValue($value, 'captureType', '') ?
                    CaptureType::fromState(static::getDataValue($value, 'captureType')) : CaptureType::unknown()
            );
        }

        return $transactionHistoryItems;
    }

    /**
     * @param Amount $amount
     *
     * @return array
     */
    private function amountToArray(Amount $amount): array
    {
        return [
            'value' => $amount->getValue(),
            'currency' => $amount->getCurrency()->getIsoCode()
        ];
    }

    /**
     * @param array $amount
     *
     * @return Amount
     *
     * @throws InvalidCurrencyCode
     */
    private function amountFromArray(array $amount): Amount
    {
        return Amount::fromInt($amount['value'], Currency::fromIsoCode($amount['currency']));
    }

    /**
     * @param PaymentLink|null $paymentLink
     *
     * @return array
     */
    private function paymentLinkToArray(?PaymentLink $paymentLink): array
    {
        return $paymentLink ? [
            'url' => $paymentLink->getUrl(),
            'expiresAt' => $paymentLink->getExpiresAt()
        ] : [];
    }

    /**
     * @param array $data
     *
     * @return void
     */
    private function paymentLinkFromArray(array $data): void
    {
        $this->transactionHistory->setPaymentLink(new DomainPaymentLink($data['url'], $data['expiresAt']));
    }
}
