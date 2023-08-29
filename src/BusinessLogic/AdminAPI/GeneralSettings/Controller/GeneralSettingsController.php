<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\GeneralSettings\Controller;

use Adyen\Core\BusinessLogic\AdminAPI\GeneralSettings\Request\GeneralSettingsRequest;
use Adyen\Core\BusinessLogic\AdminAPI\GeneralSettings\Response\GeneralSettingsGetResponse;
use Adyen\Core\BusinessLogic\AdminAPI\GeneralSettings\Response\GeneralSettingsPutResponse;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Exceptions\InvalidCaptureDelayException;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Exceptions\InvalidCaptureTypeException;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Exceptions\InvalidRetentionPeriodException;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Services\GeneralSettingsService;

/**
 * Class GeneralSettingsController
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\GeneralSettings\Controller
 */
class GeneralSettingsController
{
    /**
     * @var GeneralSettingsService
     */
    private $generalSettingsService;

    /**
     * @param GeneralSettingsService $generalSettingsService
     */
    public function __construct(GeneralSettingsService $generalSettingsService)
    {
        $this->generalSettingsService = $generalSettingsService;
    }

    /**
     * @param GeneralSettingsRequest $generalSettingsRequest
     *
     * @return GeneralSettingsPutResponse
     *
     * @throws InvalidCaptureDelayException
     * @throws InvalidRetentionPeriodException
     * @throws InvalidCaptureTypeException
     */
    public function saveGeneralSettings(GeneralSettingsRequest $generalSettingsRequest): GeneralSettingsPutResponse
    {
        $this->generalSettingsService->saveGeneralSettings($generalSettingsRequest->transformToDomainModel());

        return new GeneralSettingsPutResponse();
    }

    /**
     * @return GeneralSettingsGetResponse
     */
    public function getGeneralSettings(): GeneralSettingsGetResponse
    {
        return new GeneralSettingsGetResponse($this->generalSettingsService->getGeneralSettings());
    }
}
