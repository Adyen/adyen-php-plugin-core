<?php

namespace Adyen\Core\BusinessLogic\Domain\Webhook\Models;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;

/**
 * Class Webhook
 *
 * @package Adyen\Core\BusinessLogic\Domain\Webhook\Models
 */
class Webhook
{
    /**
     * @var Amount
     */
    private $amount;

    /**
     * @var string
     */
    private $eventCode;

    /**
     * @var string
     */
    private $eventDate;

    /**
     * @var string
     */
    private $hmacSignature;

    /**
     * @var string
     */
    private $merchantAccountCode;

    /**
     * @var string
     */
    private $merchantReference;

    /**
     * @var string
     */
    private $pspReference;

    /**
     * @var string
     */
    private $paymentMethod;

    /**
     * @var string
     */
    private $reason;

    /**
     * @var bool
     */
    private $success;

    /**
     * @var string
     */
    private $originalReference;

    /**
     * @var int
     */
    private $riskScore;

    /**
     * @var bool
     */
    private $isLive;

    /**
     * @param Amount $amount
     * @param string $eventCode
     * @param string $eventDate
     * @param string $hmacSignature
     * @param string $merchantAccountCode
     * @param string $merchantReference
     * @param string $pspReference
     * @param string $paymentMethod
     * @param string $reason
     * @param bool $success
     * @param string $originalReference
     * @param int $riskScore
     * @param bool $isLive
     */
    public function __construct(
        Amount $amount,
        string $eventCode,
        string $eventDate,
        string $hmacSignature,
        string $merchantAccountCode,
        string $merchantReference,
        string $pspReference,
        string $paymentMethod,
        string $reason,
        bool $success,
        string $originalReference,
        int $riskScore,
        bool $isLive
    ) {
        $this->amount = $amount;
        $this->eventCode = $eventCode;
        $this->eventDate = $eventDate;
        $this->hmacSignature = $hmacSignature;
        $this->merchantAccountCode = $merchantAccountCode;
        $this->merchantReference = $merchantReference;
        $this->pspReference = $pspReference;
        $this->paymentMethod = $paymentMethod;
        $this->reason = $reason;
        $this->success = $success;
        $this->originalReference = $originalReference;
        $this->riskScore = $riskScore;
        $this->isLive = $isLive;
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
    public function getEventCode(): string
    {
        return $this->eventCode;
    }

    /**
     * @return string
     */
    public function getEventDate(): string
    {
        return $this->eventDate;
    }

    /**
     * @return string
     */
    public function getHmacSignature(): string
    {
        return $this->hmacSignature;
    }

    /**
     * @return string
     */
    public function getMerchantAccountCode(): string
    {
        return $this->merchantAccountCode;
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
    public function getPspReference(): string
    {
        return $this->pspReference;
    }

    /**
     * @return string
     */
    public function getPaymentMethod(): string
    {
        return $this->paymentMethod;
    }

    /**
     * @return string
     */
    public function getReason(): string
    {
        return $this->reason;
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * @return string
     */
    public function getOriginalReference(): string
    {
        return $this->originalReference;
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
