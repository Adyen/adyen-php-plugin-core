<?php

namespace Adyen\Core\BusinessLogic\Domain\AdyenGivingSettings\Services;

use Adyen\Core\BusinessLogic\Domain\AdyenGivingSettings\Models\AdyenGivingSettings;
use Adyen\Core\BusinessLogic\Domain\AdyenGivingSettings\Repositories\AdyenGivingSettingsRepository;

/**
 * Class AdyenGivingSettingsService
 *
 * @package Adyen\Core\BusinessLogic\Domain\AdyenGivingSettings\Services
 */
class AdyenGivingSettingsService
{
    /**
     * @var AdyenGivingSettingsRepository
     */
    private $repository;

    public function __construct(AdyenGivingSettingsRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param AdyenGivingSettings $adyenGivingSettings
     *
     * @return void
     */
    public function saveAdyenGivingSettings(AdyenGivingSettings $adyenGivingSettings): void
    {
        $this->repository->setAdyenGivingSettings($adyenGivingSettings);
    }

    /**
     * @return AdyenGivingSettings|null
     */
    public function getAdyenGivingSettings(): ?AdyenGivingSettings
    {
        $settings = $this->repository->getAdyenGivingSettings();

        return $settings ?: new AdyenGivingSettings(false);
    }
}
