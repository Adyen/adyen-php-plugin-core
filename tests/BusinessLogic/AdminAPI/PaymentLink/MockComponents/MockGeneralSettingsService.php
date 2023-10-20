<?php

namespace Adyen\Core\Tests\BusinessLogic\AdminAPI\PaymentLink\MockComponents;

use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Exceptions\InvalidCaptureDelayException;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Exceptions\InvalidRetentionPeriodException;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Models\CaptureType as CaptureTypeModel;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Models\GeneralSettings;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Services\GeneralSettingsService;

/**
 * Class MockGeneralSettingsService
 *
 * @package Adyen\Core\Tests\BusinessLogic\AdminAPI\PaymentLink\MockComponents
 */
class MockGeneralSettingsService extends GeneralSettingsService
{
    /**
     * @return GeneralSettings|null
     *
     * @throws InvalidCaptureDelayException
     * @throws InvalidRetentionPeriodException
     */
    public function getGeneralSettings(): ?GeneralSettings
    {
        return new GeneralSettings(
            true,
            CaptureTypeModel::delayed(),
            1,
            's',
            60,
            true,
            'Title',
            7
        );
    }
}
