<?php

namespace Adyen\Core\BusinessLogic\Domain\GeneralSettings\Models;

use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Exceptions\InvalidCaptureDelayException;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Exceptions\InvalidDefaultExpirationTimeException;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Exceptions\InvalidRetentionPeriodException;
use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class GeneralSettings
 *
 * @package Adyen\Core\BusinessLogic\Domain\GeneralSettings\Models
 */
class GeneralSettings
{
    /**
     * @var bool
     */
    private $basketItemSync;

    /**
     * @var CaptureType
     */
    private $captureType;

    /**
     * @var int
     */
    private $captureDelay;

    /**
     * @var string
     */
    private $shipmentStatus;

    /**
     * @var int
     */
    private $retentionPeriod;

    /**
     * @var bool
     */
    private $enablePayByLink;

    /**
     * @var string
     */
    private $payByLinkTitle;

    /**
     * @var int
     */
    private $defaultLinkExpirationTime;

    /**
     * @var bool
     */
    private $executeOrderUpdateSynchronously;
    /**
     * @var bool
     */
    private $cancelledPartialPayment;
    /**
     * @var bool
     */
    private $disabledOrderModificationsForFailedRefund;

    /**
     * @param bool $basketItemSync
     * @param CaptureType $captureType
     * @param string $captureDelay
     * @param string $shipmentStatus
     * @param string $retentionPeriod
     * @param bool $enablePayByLink
     * @param string $payByLinkTitle
     * @param string $defaultLinkExpirationTime
     * @param bool $executeOrderUpdateSynchronously
     * @param bool $cancelledPartialPayment
     * @param bool $disabledOrderModificationsForFailedRefund
     * @throws InvalidCaptureDelayException
     * @throws InvalidDefaultExpirationTimeException
     * @throws InvalidRetentionPeriodException
     */
    public function __construct(
        bool        $basketItemSync,
        CaptureType $captureType,
        string      $captureDelay = '1',
        string      $shipmentStatus = '',
        string      $retentionPeriod = '60',
        bool        $enablePayByLink = true,
        string      $payByLinkTitle = 'Adyen Pay By Link',
        string      $defaultLinkExpirationTime = '7',
        bool        $executeOrderUpdateSynchronously = false,
        bool        $cancelledPartialPayment = true,
        bool        $disabledOrderModificationsForFailedRefund = false
    )
    {
        $this->validate($captureDelay, $retentionPeriod, $defaultLinkExpirationTime);

        $this->basketItemSync = $basketItemSync;
        $this->captureType = $captureType;
        $this->captureDelay = $captureDelay;
        $this->shipmentStatus = $shipmentStatus;
        $this->retentionPeriod = $retentionPeriod;
        $this->enablePayByLink = $enablePayByLink;
        $this->payByLinkTitle = $payByLinkTitle;
        $this->defaultLinkExpirationTime = $defaultLinkExpirationTime;
        $this->executeOrderUpdateSynchronously = $executeOrderUpdateSynchronously;
        $this->cancelledPartialPayment = $cancelledPartialPayment;
        $this->disabledOrderModificationsForFailedRefund = $disabledOrderModificationsForFailedRefund;
    }

    /**
     * @return bool
     */
    public function isBasketItemSync(): bool
    {
        return $this->basketItemSync;
    }

    /**
     * @return CaptureType
     */
    public function getCapture(): CaptureType
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
     * @return string
     */
    public function getShipmentStatus(): string
    {
        return $this->shipmentStatus;
    }

    /**
     * @return int
     */
    public function getRetentionPeriod(): int
    {
        return $this->retentionPeriod;
    }

    /**
     * @return int
     */
    public function getCaptureDelayHours(): int
    {
        if ($this->getCapture()->equal(CaptureType::manual())) {
            return -1;
        }

        if ($this->getCapture()->equal(CaptureType::delayed())) {
            return $this->captureDelay * 24;
        }

        return 0;
    }

    /**
     * @return bool
     */
    public function isEnablePayByLink(): bool
    {
        return $this->enablePayByLink;
    }

    /**
     * @return string
     */
    public function getPayByLinkTitle(): string
    {
        return $this->payByLinkTitle;
    }

    /**
     * @return int
     */
    public function getDefaultLinkExpirationTime(): int
    {
        return $this->defaultLinkExpirationTime;
    }

    /**
     * @return bool
     */
    public function isExecuteOrderUpdateSynchronously(): bool
    {
        return $this->executeOrderUpdateSynchronously;
    }

    public function isCancelledPartialPayment(): bool
    {
        return $this->cancelledPartialPayment;
    }

    public function areDisabledOrderModificationsForFailedRefund(): bool
    {
        return $this->disabledOrderModificationsForFailedRefund;
    }

    /**
     * Validates capture delay and retention period values.
     * Capture delay must be between 1 and 7.
     * Retention period must be at least 60.
     *
     * @throws InvalidCaptureDelayException
     * @throws InvalidRetentionPeriodException
     * @throws InvalidDefaultExpirationTimeException
     */
    private function validate(string $captureDelay, string $retentionPeriod, string $defaultLinkExpirationTime): void
    {
        if (!ctype_digit($captureDelay) || $captureDelay < 1 || $captureDelay > 7) {
            throw new InvalidCaptureDelayException(
                new TranslatableLabel('Capture delay number of days must be between 1 and 7', 'generalSettings.captureDelayError')
            );
        }

        if (!ctype_digit($retentionPeriod) || $retentionPeriod < 60) {
            throw new InvalidRetentionPeriodException(
                new TranslatableLabel('Minimum number of retention period is 60.', 'generalSettings.retentionPeriodError')
            );
        }

        if (!ctype_digit($defaultLinkExpirationTime) || $defaultLinkExpirationTime < 1) {
            throw new InvalidDefaultExpirationTimeException(
                new TranslatableLabel('Default link expiration time must me greater than 1.', 'generalSettings.defaultLinkExpirationTime')
            );
        }
    }
}
