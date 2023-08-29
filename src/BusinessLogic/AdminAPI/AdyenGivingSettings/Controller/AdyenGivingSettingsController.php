<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\AdyenGivingSettings\Controller;

use Adyen\Core\BusinessLogic\AdminAPI\AdyenGivingSettings\Request\AdyenGivingSettingsRequest;
use Adyen\Core\BusinessLogic\AdminAPI\AdyenGivingSettings\Response\AdyenGivingSettingsGetResponse;
use Adyen\Core\BusinessLogic\AdminAPI\AdyenGivingSettings\Response\AdyenGivingSettingsPutResponse;
use Adyen\Core\BusinessLogic\Domain\AdyenGivingSettings\Services\AdyenGivingSettingsService;

/**
 * Class AdyenGivingSettingsController
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\AdyenGivingSettings\Controller
 */
class AdyenGivingSettingsController
{
    /**
     * @var AdyenGivingSettingsService
     */
    private $adyenGivingSettingsService;

    public function __construct(AdyenGivingSettingsService $adyenGivingSettingsService)
    {
        $this->adyenGivingSettingsService = $adyenGivingSettingsService;
    }

    /**
     * @return AdyenGivingSettingsGetResponse
     */
    public function getAdyenGivingSettings(): AdyenGivingSettingsGetResponse
    {
        return new AdyenGivingSettingsGetResponse($this->adyenGivingSettingsService->getAdyenGivingSettings());
    }

    /**
     * @param AdyenGivingSettingsRequest $adyenGivingSettingsRequest
     *
     * @return AdyenGivingSettingsPutResponse
     */
    public function saveAdyenGivingSettings(AdyenGivingSettingsRequest $adyenGivingSettingsRequest
    ): AdyenGivingSettingsPutResponse {
        $this->adyenGivingSettingsService->saveAdyenGivingSettings(
            $adyenGivingSettingsRequest->transformToDomainModel()
        );

        return new AdyenGivingSettingsPutResponse();
    }
}
