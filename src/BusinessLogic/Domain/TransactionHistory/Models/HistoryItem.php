<?php

namespace Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Models\CaptureType;

/**
 * Class HistoryItem
 *
 * @package Adyen\Core\BusinessLogic\Domain\TransactionHistoryHistoryHistory\Models
 */
class HistoryItem
{
    /**
     * @var string
     */
    private $pspReference;
    /**
     * @var string
     */
    private $authorizationPspReference;

    /**
     * @var string
     */
    private $merchantReference;

    /**
     * @var string
     */
    private $eventCode;

    /**
     * @var string
     */
    private $paymentState;

    /**
     * @var string
     */
    private $dateAndTime;

    /**
     * @var Amount
     */
    private $amount;

    /**
     * @var bool
     */
    private $status;

    /**
     * @var string
     */
    private $paymentMethod;

    /**
     * @var int
     */
    private $riskScore;

    /**
     * @var bool
     */
    private $isLive;
    /**
     * @var CaptureType
     */
    private $captureType;

    /**
     * @var int|null
     */
    private $retryCount;

    /**
     * @var int|null
     */
    private $startedAt;

    /**
     * @var int|null
     */
    private $transactionLogId;

    /**
     * @param string $pspReference
     * @param string $merchantReference
     * @param string $eventCode
     * @param string $paymentState
     * @param string $dateAndTime
     * @param bool $status
     * @param Amount $amount
     * @param string $paymentMethod
     * @param int $riskScore
     * @param bool $isLive
     * @param string $authorizationPspReference
     * @param CaptureType|null $captureType
     * @param int $retryCount
     * @param int|null $startedAt
     * @param int|null $transactionLogId
     */
    public function __construct(
        string      $pspReference,
        string      $merchantReference,
        string      $eventCode,
        string      $paymentState,
        string      $dateAndTime,
        bool        $status,
        Amount      $amount,
        string      $paymentMethod,
        int         $riskScore,
        bool        $isLive,
        string      $authorizationPspReference = '',
        CaptureType $captureType = null,
        int         $retryCount = 1,
        int         $startedAt = null,
        int         $transactionLogId = null
    )
    {
        $this->pspReference = $pspReference;
        $this->merchantReference = $merchantReference;
        $this->eventCode = $eventCode;
        $this->paymentState = $paymentState;
        $this->dateAndTime = $dateAndTime;
        $this->status = $status;
        $this->amount = $amount;
        $this->paymentMethod = $paymentMethod;
        $this->riskScore = $riskScore;
        $this->isLive = $isLive;
        $this->authorizationPspReference = $authorizationPspReference;
        $this->captureType = $captureType;
        $this->retryCount = $retryCount;
        $this->startedAt = $startedAt;
        $this->transactionLogId = $transactionLogId;
    }

    /**
     * @return string
     */
    public function getPspReference(): string
    {
        return $this->pspReference;
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
    public function getEventCode(): string
    {
        return $this->eventCode;
    }

    /**
     * @return string
     */
    public function getPaymentState(): string
    {
        return $this->paymentState;
    }

    /**
     * @return string
     */
    public function getDateAndTime(): string
    {
        return $this->dateAndTime;
    }

    /**
     * @return bool
     */
    public function getStatus(): bool
    {
        return $this->status;
    }

    /**
     * @return Amount
     */
    public function getAmount(): Amount
    {
        return $this->amount;
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
     * @return bool
     */
    public function isLive(): bool
    {
        return $this->isLive;
    }

    /**
     * @return string
     */
    public function getAuthorizationPspReference(): string
    {
        return $this->authorizationPspReference;
    }

    /**
     * @return CaptureType|null
     */
    public function getCaptureType(): ?CaptureType
    {
        return $this->captureType;
    }

    /**
     * @return int
     */
    public function getRetryCount(): int
    {
        return $this->retryCount ?? 0;
    }

    /**
     * @return void
     */
    public function incrementRetryCount(): void
    {
        $this->retryCount++;
    }

    /**
     * @return int
     */
    public function getStartedAt(): ?int
    {
        return $this->startedAt;
    }

    /**
     * @return void
     */
    public function setStartedAt(int $timestamp): void
    {
        $this->startedAt = $timestamp;
    }

    /**
     * @return int
     */
    public function getTransactionLogId(): ?int
    {
        return $this->transactionLogId;
    }

    /**
     * @return void
     */
    public function setTransactionLogId(int $logId): void
    {
        $this->transactionLogId = $logId;
    }
}
