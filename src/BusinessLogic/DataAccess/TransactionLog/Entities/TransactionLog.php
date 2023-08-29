<?php

namespace Adyen\Core\BusinessLogic\DataAccess\TransactionLog\Entities;

use Adyen\Core\Infrastructure\ORM\Configuration\EntityConfiguration;
use Adyen\Core\Infrastructure\ORM\Configuration\IndexMap;
use Adyen\Core\Infrastructure\ORM\Entity;

/**
 * Class TransactionLog
 *
 * @package Adyen\Core\BusinessLogic\DataAccess\TransactionLog\Entities
 */
class TransactionLog extends Entity
{
    /**
     * Fully qualified name of this class.
     */
    public const CLASS_NAME = __CLASS__;

    /**
     * @var string
     */
    protected $storeId = '';

    /**
     * @var string
     */
    protected $merchantReference;

    /**
     * @var int
     */
    protected $executionId;

    /**
     * @var string
     */
    protected $paymentMethod;

    /**
     * @var int
     */
    protected $timestamp;

    /**
     * @var string
     */
    protected $eventCode;

    /**
     * @var bool
     */
    protected $isSuccessful;

    /**
     * @var string
     */
    protected $queueStatus;

    /**
     * @var string
     */
    protected $reason;

    /**
     * @var ?string
     */
    protected $failureDescription;

    /**
     * @var string
     */
    protected $adyenLink;

    /**
     * @var string
     */
    protected $shopLink;
    /**
     * @var string
     */
    protected $pspReference;
    /**
     * @var string[]
     */
    protected $fields = [
        'id',
        'storeId',
        'merchantReference',
        'executionId',
        'isSuccessful',
        'reason',
        'eventCode',
        'paymentMethod',
        'timestamp',
        'queueStatus',
        'failureDescription',
        'adyenLink',
        'shopLink',
        'pspReference'
    ];

    /**
     * @return EntityConfiguration
     */
    public function getConfig(): EntityConfiguration
    {
        $indexMap = new IndexMap();
        $indexMap->addStringIndex('storeId')
            ->addStringIndex('merchantReference')
            ->addIntegerIndex('executionId')
            ->addIntegerIndex('timestamp');

        return new EntityConfiguration($indexMap, 'TransactionLog');
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
     * @return int
     */
    public function getExecutionId(): int
    {
        return $this->executionId;
    }

    /**
     * @param int $executionId
     */
    public function setExecutionId(int $executionId): void
    {
        $this->executionId = $executionId;
    }

    /**
     * @return string
     */
    public function getPaymentMethod(): string
    {
        return $this->paymentMethod;
    }

    /**
     * @param string $paymentMethod
     */
    public function setPaymentMethod(string $paymentMethod): void
    {
        $this->paymentMethod = $paymentMethod;
    }

    /**
     * @return int
     */
    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    /**
     * @param int $timestamp
     */
    public function setTimestamp(int $timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    /**
     * @return string
     */
    public function getEventCode(): string
    {
        return $this->eventCode;
    }

    /**
     * @param string $eventCode
     */
    public function setEventCode(string $eventCode): void
    {
        $this->eventCode = $eventCode;
    }

    /**
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return $this->isSuccessful;
    }

    /**
     * @param bool $isSuccessful
     */
    public function setIsSuccessful(bool $isSuccessful): void
    {
        $this->isSuccessful = $isSuccessful;
    }

    /**
     * @return string
     */
    public function getQueueStatus(): string
    {
        return $this->queueStatus;
    }

    /**
     * @param string $queueStatus
     */
    public function setQueueStatus(string $queueStatus): void
    {
        $this->queueStatus = $queueStatus;
    }

    /**
     * @return ?string
     */
    public function getReason(): ?string
    {
        return $this->reason;
    }

    /**
     * @param ?string $reason
     */
    public function setReason(?string $reason): void
    {
        $this->reason = $reason;
    }

    /**
     * @return ?string
     */
    public function getFailureDescription(): ?string
    {
        return $this->failureDescription;
    }

    /**
     * @param ?string $failureDescription
     */
    public function setFailureDescription(?string $failureDescription): void
    {
        $this->failureDescription = $failureDescription;
    }

    /**
     * @return ?string
     */
    public function getAdyenLink(): ?string
    {
        return $this->adyenLink;
    }

    /**
     * @param ?string $adyenLink
     */
    public function setAdyenLink(?string $adyenLink): void
    {
        $this->adyenLink = $adyenLink;
    }

    /**
     * @return ?string
     */
    public function getShopLink(): ?string
    {
        return $this->shopLink;
    }

    /**
     * @param ?string $shopLink
     */
    public function setShopLink(?string $shopLink): void
    {
        $this->shopLink = $shopLink;
    }

    /**
     * @return string
     */
    public function getPspReference(): string
    {
        return $this->pspReference;
    }

    /**
     * @param string $pspReference
     */
    public function setPspReference(string $pspReference): void
    {
        $this->pspReference = $pspReference;
    }
}
