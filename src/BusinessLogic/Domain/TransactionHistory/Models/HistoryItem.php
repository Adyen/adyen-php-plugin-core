<?php

namespace Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;

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
     */
    public function __construct(
        string $pspReference,
        string $merchantReference,
        string $eventCode,
        string $paymentState,
        string $dateAndTime,
        bool $status,
        Amount $amount,
        string $paymentMethod,
        int $riskScore,
        bool $isLive
    ) {
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
}
