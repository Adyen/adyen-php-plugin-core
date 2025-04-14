<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\GeneralSettings\Request;

use Adyen\Core\BusinessLogic\AdminAPI\Request\Request;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Exceptions\InvalidCaptureDelayException;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Exceptions\InvalidDefaultExpirationTimeException;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Exceptions\InvalidRetentionPeriodException;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Models\CaptureType;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Models\GeneralSettings;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Exceptions\InvalidCaptureTypeException;

/**
 * Class GeneralSettingsRequest
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\GeneralSettings\Request
 */
class GeneralSettingsRequest extends Request
{
    /**
     * @var bool
     */
    private $basketItemSync;

    /**
     * @var string
     */
    private $captureType;

    /**
     * @var string
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
     * @param string $captureType
     * @param string $captureDelay
     * @param string $shipmentStatus
     * @param string $retentionPeriod
     * @param bool $enablePayByLink
     * @param string $payByLinkTitle
     * @param string $defaultLinkExpirationTime
     * @param bool $executeOrderUpdateSynchronously
     * @param bool $cancelledPartialPayment
     * @param bool $disabledOrderModificationsForFailedRefund
     */
    public function __construct(
        bool   $basketItemSync,
        string $captureType,
        string $captureDelay = '1',
        string $shipmentStatus = '',
        string $retentionPeriod = '60',
        bool   $enablePayByLink = true,
        string $payByLinkTitle = '',
        string $defaultLinkExpirationTime = '7',
        bool   $executeOrderUpdateSynchronously = false,
        bool   $cancelledPartialPayment = true,
        bool   $disabledOrderModificationsForFailedRefund = false
    )
    {
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
     * Transform to Domain model
     *
     * @return GeneralSettings
     *
     * @throws InvalidCaptureDelayException
     * @throws InvalidRetentionPeriodException
     * @throws InvalidCaptureTypeException|
     * @throws InvalidDefaultExpirationTimeException
     */
    public function transformToDomainModel(): object
    {
        return new GeneralSettings(
            $this->basketItemSync,
            CaptureType::fromState($this->captureType),
            $this->captureDelay,
            $this->shipmentStatus,
            $this->retentionPeriod,
            $this->enablePayByLink,
            !empty($this->payByLinkTitle) ? $this->payByLinkTitle : 'Adyen Pay By Link',
            !empty($this->defaultLinkExpirationTime) ? $this->defaultLinkExpirationTime : '7',
            $this->executeOrderUpdateSynchronously,
            $this->cancelledPartialPayment,
            $this->disabledOrderModificationsForFailedRefund
        );
    }
}
