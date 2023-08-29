<?php

namespace Adyen\Core\Tests\BusinessLogic\AdminAPI\AdyenGivingSettings;

use Adyen\Core\BusinessLogic\AdminAPI\AdminAPI;
use Adyen\Core\BusinessLogic\AdminAPI\AdyenGivingSettings\Controller\AdyenGivingSettingsController;
use Adyen\Core\BusinessLogic\AdminAPI\AdyenGivingSettings\Request\AdyenGivingSettingsRequest;
use Adyen\Core\BusinessLogic\AdminAPI\AdyenGivingSettings\Response\AdyenGivingSettingsGetResponse;
use Adyen\Core\BusinessLogic\Bootstrap\SingleInstance;
use Adyen\Core\BusinessLogic\Domain\AdyenGivingSettings\Models\AdyenGivingSettings as AdyenGivingSettingsModel;
use Adyen\Core\BusinessLogic\Domain\AdyenGivingSettings\Repositories\AdyenGivingSettingsRepository as AdyenGivingSettingsRepositoryInterface;
use Adyen\Core\BusinessLogic\Domain\AdyenGivingSettings\Services\AdyenGivingSettingsService;
use Adyen\Core\Tests\BusinessLogic\AdminAPI\AdyenGivingSettings\MockComponents\MockAdyenGivingSettingsRepository;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;
use Exception;

/**
 * Class AdyenGivingSettingsApiTest
 *
 * @package Adyen\Core\Tests\BusinessLogic\AdminAPI\AdyenGivingSettings
 */
class AdyenGivingSettingsApiTest extends BaseTestCase
{
    /**
     * @var MockAdyenGivingSettingsRepository
     */
    private $adyenGivingSettingsRepository;

    /**
     * @var AdyenGivingSettingsService
     */
    private $service;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->adyenGivingSettingsRepository = new MockAdyenGivingSettingsRepository();
        $this->service = TestServiceRegister::getService(AdyenGivingSettingsService::class);

        TestServiceRegister::registerService(
            AdyenGivingSettingsRepositoryInterface::class,
            new SingleInstance(function () {
                return $this->adyenGivingSettingsRepository;
            })
        );

        TestServiceRegister::registerService(
            AdyenGivingSettingsController::class,
            new SingleInstance(function () {
                return new AdyenGivingSettingsController(
                    TestServiceRegister::getService(AdyenGivingSettingsService::class)
                );
            })
        );
    }

    /**
     * @return void
     */
    public function testIsGetResponseSuccessful(): void
    {
        // Arrange
        $this->adyenGivingSettingsRepository->setAdyenGivingSettings(
            new AdyenGivingSettingsModel(
                true,
                'name',
                'desc',
                'acc',
                [1, 2, 3],
                'website',
                'logo',
                'img'
            )
        );
        // Act
        $response = AdminAPI::get()->adyenGivingSettings('1')->getAdyenGivingSettings();

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @return void
     */
    public function testGetResponse(): void
    {
        // Arrange
        $settings = new AdyenGivingSettingsModel(
            true,
            'name',
            'desc',
            'acc',
            [1, 2, 3],
            'website',
            'logo',
            'img'
        );
        $this->adyenGivingSettingsRepository->setAdyenGivingSettings(
            $settings
        );

        $expectedResponse = new AdyenGivingSettingsGetResponse($settings);

        // Act
        $response = AdminAPI::get()->adyenGivingSettings('1')->getAdyenGivingSettings();

        // Assert
        self::assertEquals($expectedResponse, $response);
    }

    /**
     * @return void
     */
    public function testGetResponseToArray(): void
    {
        // Arrange
        $settings = new AdyenGivingSettingsModel(
            true,
            'name',
            'desc',
            'acc',
            [1, 2, 3],
            'website',
            'logo',
            'img'
        );
        $this->adyenGivingSettingsRepository->setAdyenGivingSettings(
            $settings
        );

        // Act
        $response = AdminAPI::get()->adyenGivingSettings('1')->getAdyenGivingSettings();

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
        $settingsRequest = new AdyenGivingSettingsRequest(
            true,
            'name',
            'desc',
            'acc',
            '1,2,3',
            'website',
            'logo',
            'img'
        );

        // Act
        $response = AdminAPI::get()->adyenGivingSettings('1')->saveAdyenGivingSettings($settingsRequest);

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @return void
     */
    public function testPutResponseToArray(): void
    {
        // Arrange
        $settingsRequest = new AdyenGivingSettingsRequest(
            true,
            'name',
            'desc',
            'acc',
            '1,2,3',
            'website',
            'logo',
            'img'
        );

        // Act
        $response = AdminAPI::get()->adyenGivingSettings('1')->saveAdyenGivingSettings($settingsRequest);

        // Assert
        self::assertEquals(['success' => true], $response->toArray());
    }

    /**
     * @return array
     */
    private function expectedToArrayResponse(): array
    {
        return [
            'enableAdyenGiving' => true,
            'charityName' => 'name',
            'charityDescription' => 'desc',
            'charityMerchantAccount' => 'acc',
            'donationAmount' => '1,2,3',
            'charityWebsite' => 'website',
            'logo' => 'logo',
            'backgroundImage' => 'img'
        ];
    }
}
