<?php

namespace Adyen\Core\Tests\BusinessLogic\AdminAPI\GeneralSettings;

use Adyen\Core\BusinessLogic\AdminAPI\AdminAPI;
use Adyen\Core\BusinessLogic\AdminAPI\GeneralSettings\Controller\GeneralSettingsController;
use Adyen\Core\BusinessLogic\AdminAPI\GeneralSettings\Request\GeneralSettingsRequest;
use Adyen\Core\BusinessLogic\AdminAPI\GeneralSettings\Response\GeneralSettingsGetResponse;
use Adyen\Core\BusinessLogic\Bootstrap\SingleInstance;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Exceptions\InvalidCaptureDelayException;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Exceptions\InvalidCaptureTypeException;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Exceptions\InvalidRetentionPeriodException;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Models\CaptureType as CaptureTypeModel;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Models\GeneralSettings;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Models\GeneralSettings as GeneralSettingsModel;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Repositories\GeneralSettingsRepository;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Services\GeneralSettingsService;
use Adyen\Core\Tests\BusinessLogic\AdminAPI\GeneralSettings\MockComponents\MockGeneralSettingsRepository;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class GeneralSettingsApiTest
 *
 * @package Adyen\Core\Tests\BusinessLogic\AdminAPI\GeneralSettings
 */
class GeneralSettingsApiTest extends BaseTestCase
{
    /**
     * @var MockGeneralSettingsRepository
     */
    private $generalSettingsRepository;

    /**
     * @var GeneralSettingsService
     */
    private $service;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->generalSettingsRepository = new MockGeneralSettingsRepository();
        $this->service = TestServiceRegister::getService(GeneralSettingsService::class);

        TestServiceRegister::registerService(
            GeneralSettingsRepository::class,
            new SingleInstance(function () {
                return $this->generalSettingsRepository;
            })
        );

        TestServiceRegister::registerService(
            GeneralSettingsController::class,
            new SingleInstance(function () {
                return new GeneralSettingsController(TestServiceRegister::getService(GeneralSettingsService::class));
            })
        );
    }

    /**
     * @throws InvalidCaptureDelayException
     * @throws InvalidRetentionPeriodException
     */
    public function testIsGetResponseSuccessful(): void
    {
        // Arrange
        $this->generalSettingsRepository->setMockGeneralSettings(
            new GeneralSettingsModel(
                true,
                CaptureTypeModel::delayed(),
                1,
                's',
                60
            )
        );

        // Act
        $response = AdminAPI::get()->generalSettings('1')->getGeneralSettings();

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @throws InvalidCaptureDelayException
     * @throws InvalidRetentionPeriodException
     */
    public function testGetResponse(): void
    {
        // Arrange
        $settings = new GeneralSettingsModel(
            true,
            CaptureTypeModel::delayed(),
            1,
            's',
            60
        );
        $this->generalSettingsRepository->setMockGeneralSettings(
            $settings
        );
        $expectedResponse = new GeneralSettingsGetResponse($settings);

        // Act
        $response = AdminAPI::get()->generalSettings('1')->getGeneralSettings();

        // Assert
        self::assertEquals($expectedResponse, $response);
    }

    /**
     * @throws InvalidCaptureDelayException
     * @throws InvalidRetentionPeriodException
     */
    public function testGetResponseToArray(): void
    {
        // Arrange
        $settings = new GeneralSettingsModel(
            true,
            CaptureTypeModel::delayed(),
            1,
            's',
            60
        );
        $this->generalSettingsRepository->setMockGeneralSettings(
            $settings
        );

        // Act
        $response = AdminAPI::get()->generalSettings('1')->getGeneralSettings();

        // Assert
        self::assertEquals($response->toArray(), $this->expectedToArrayResponse());
    }

    /**
     * @throws InvalidCaptureDelayException
     * @throws InvalidRetentionPeriodException
     * @throws InvalidCaptureTypeException
     */
    public function testIsPutResponseSuccessful(): void
    {
        // Arrange
        $settingsRequest = new GeneralSettingsRequest(
            true,
            'delayed',
            1,
            's',
            60
        );

        // Act
        $response = AdminAPI::get()->generalSettings('1')->saveGeneralSettings($settingsRequest);

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @throws InvalidCaptureDelayException
     * @throws InvalidRetentionPeriodException
     * @throws InvalidCaptureTypeException
     */
    public function testPutResponseToArray(): void
    {
        // Arrange
        $settingsRequest = new GeneralSettingsRequest(
            true,
            'delayed',
            1,
            's',
            60
        );

        // Act
        $response = AdminAPI::get()->generalSettings('1')->saveGeneralSettings($settingsRequest);

        // Assert
        self::assertEquals(['success' => true], $response->toArray());
    }

    /**
     * @return array
     */
    private function expectedToArrayResponse(): array
    {
        return [
            'basketItemSync' => true,
            'capture' => 'delayed',
            'captureDelay' => 1,
            'shipmentStatus' => 's',
            'retentionPeriod' => 60
        ];
    }
}
