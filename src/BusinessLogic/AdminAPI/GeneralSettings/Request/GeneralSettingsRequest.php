<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\GeneralSettings\Request;

use Adyen\Core\BusinessLogic\AdminAPI\Request\Request;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Exceptions\InvalidCaptureDelayException;
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
     * @param bool $basketItemSync
     * @param string $captureType
     * @param string $captureDelay
     * @param string $shipmentStatus
     * @param string $retentionPeriod
     */
    public function __construct(
        bool $basketItemSync,
        string $captureType,
        string $captureDelay = '1',
        string $shipmentStatus = '',
        string $retentionPeriod = '60'
    ) {
        $this->basketItemSync = $basketItemSync;
        $this->captureType = $captureType;
        $this->captureDelay = $captureDelay;
        $this->shipmentStatus = $shipmentStatus;
        $this->retentionPeriod = $retentionPeriod;
    }

    /**
     * Transform to Domain model
     *
     * @return GeneralSettings
     *
     * @throws InvalidCaptureDelayException
     * @throws InvalidRetentionPeriodException
     * @throws InvalidCaptureTypeException
     */
    public function transformToDomainModel(): object
    {
        return new GeneralSettings(
            $this->basketItemSync,
            CaptureType::fromState($this->captureType),
            $this->captureDelay,
            $this->shipmentStatus,
            $this->retentionPeriod
        );
    }
}
