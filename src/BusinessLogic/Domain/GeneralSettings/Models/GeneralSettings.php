<?php

namespace Adyen\Core\BusinessLogic\Domain\GeneralSettings\Models;

use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Exceptions\InvalidCaptureDelayException;
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
     * @param bool $basketItemSync
     * @param CaptureType $captureType
     * @param string $captureDelay
     * @param string $shipmentStatus
     * @param string $retentionPeriod
     * @param bool $enablePayByLink
     * @param string $payByLinkTitle
     * @param string $defaultLinkExpirationTime
     *
     * @throws InvalidCaptureDelayException
     * @throws InvalidRetentionPeriodException
     */
    public function __construct(
        bool $basketItemSync,
        CaptureType $captureType,
        string $captureDelay = '1',
        string $shipmentStatus = '',
        string $retentionPeriod = '60',
        bool $enablePayByLink = true,
        string $payByLinkTitle = 'Adyen Pay By Link',
        string $defaultLinkExpirationTime = '7'
    ) {
        $this->validate($captureDelay, $retentionPeriod);

        $this->basketItemSync = $basketItemSync;
        $this->captureType = $captureType;
        $this->captureDelay = $captureDelay;
        $this->shipmentStatus = $shipmentStatus;
        $this->retentionPeriod = $retentionPeriod;
        $this->enablePayByLink = $enablePayByLink;
        $this->payByLinkTitle = $payByLinkTitle;
        $this->defaultLinkExpirationTime = $defaultLinkExpirationTime;
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
        if($this->getCapture()->equal(CaptureType::manual())) {
            return -1;
        }

        if($this->getCapture()->equal(CaptureType::delayed())) {
            return $this->captureDelay * 24;
        }

        return 0;
    }

    public function isEnablePayByLink(): bool
    {
        return $this->enablePayByLink;
    }

    public function getPayByLinkTitle(): string
    {
        return $this->payByLinkTitle;
    }

    /**
     * @return int|string
     */
    public function getDefaultLinkExpirationTime()
    {
        return $this->defaultLinkExpirationTime;
    }

    /**
     * Validates capture delay and retention period values.
     * Capture delay must be between 1 and 7.
     * Retention period must be at least 60.
     *
     * @throws InvalidCaptureDelayException
     * @throws InvalidRetentionPeriodException
     */
    private function validate(string $captureDelay, string $retentionPeriod): void
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
    }
}
