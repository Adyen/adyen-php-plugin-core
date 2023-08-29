<?php

namespace Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events;

use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Adyen\Core\Infrastructure\Utility\TimeProvider;
use DateTime;

/**
 * Class Notification
 *
 * @package Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models
 */
abstract class Event
{
    /**
     * @var string
     */
    private $orderId;

    /**
     * @var string
     */
    private $paymentMethod;

    /**
     * @var Severity
     */
    private $severity;

    /**
     * @var DateTime
     */
    private $dateAndTime;

    /**
     * @var TranslatableLabel
     */
    private $message;

    /**
     * @var TranslatableLabel
     */
    private $details;

    /**
     * @param string $orderId
     * @param string $paymentMethod
     * @param Severity $severity
     * @param TranslatableLabel $message
     * @param TranslatableLabel $details
     */
    public function __construct(
        string $orderId,
        string $paymentMethod,
        Severity $severity,
        TranslatableLabel $message,
        TranslatableLabel $details
    ) {
        $this->orderId = $orderId;
        $this->paymentMethod = $paymentMethod;
        $this->severity = $severity;
        $this->message = $message;
        $this->details = $details;
        $this->dateAndTime = TimeProvider::getInstance()->getCurrentLocalTime();
    }

    /**
     * @return string
     */
    public function getOrderId(): string
    {
        return $this->orderId;
    }

    /**
     * @return string
     */
    public function getPaymentMethod(): string
    {
        return $this->paymentMethod;
    }

    /**
     * @return Severity
     */
    public function getSeverity(): Severity
    {
        return $this->severity;
    }

    /**
     * @return DateTime
     */
    public function getDateAndTime(): DateTime
    {
        return $this->dateAndTime;
    }

    /**
     * @return TranslatableLabel
     */
    public function getMessage(): TranslatableLabel
    {
        return $this->message;
    }

    /**
     * @return TranslatableLabel
     */
    public function getDetails(): TranslatableLabel
    {
        return $this->details;
    }
}
