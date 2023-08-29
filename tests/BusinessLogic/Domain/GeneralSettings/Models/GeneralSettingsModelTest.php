<?php

namespace Adyen\Core\Tests\BusinessLogic\Domain\GeneralSettings\Models;

use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Exceptions\InvalidCaptureDelayException;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Exceptions\InvalidCaptureTypeException;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Exceptions\InvalidRetentionPeriodException;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Models\CaptureType as CaptureTypeModel;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Models\GeneralSettings as GeneralSettingsModel;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Exception;

/**
 * Class GeneralSettingsModelTest
 *
 * @package Adyen\Core\Tests\BusinessLogic\Domain\GeneralSettings\Model
 */
class GeneralSettingsModelTest extends BaseTestCase
{
    /**
     * @throws Exception
     */
    public function testInvalidRetentionPeriodException(): void
    {
        // arrange
        $this->expectException(InvalidRetentionPeriodException::class);

        // act
        new GeneralSettingsModel(
            true,
            CaptureTypeModel::delayed(),
            1,
            's',
            1
        );
        // assert
    }

    /**
     * @throws Exception
     */
    public function testCaptureDelayException(): void
    {
        // arrange
        $this->expectException(InvalidCaptureDelayException::class);

        // act
        new GeneralSettingsModel(
            true,
            CaptureTypeModel::delayed(),
            8,
            's',
            60
        );
        // assert
    }

    /**
     * @throws Exception
     */
    public function testInvalidCaptureTypeException(): void
    {
        // arrange
        $this->expectException(InvalidCaptureTypeException::class);

        // act
        new GeneralSettingsModel(
            true,
            CaptureTypeModel::fromState('1'),
            8,
            's',
            60
        );
        // assert
    }
}
