<?php

namespace Adyen\Core\BusinessLogic\Domain\GeneralSettings\Services;

use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Models\GeneralSettings;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Repositories\GeneralSettingsRepository;

/**
 * Class GeneralSettingsService
 *
 * @package Adyen\Core\BusinessLogic\Domain\GeneralSettings\Services
 */
class GeneralSettingsService
{
    /**
     * @var GeneralSettingsRepository
     */
    private $repository;

    public function __construct(GeneralSettingsRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param GeneralSettings $generalSettings
     *
     * @return void
     */
    public function saveGeneralSettings(GeneralSettings $generalSettings): void
    {
        $this->repository->setGeneralSettings($generalSettings);
    }

    /**
     * @return GeneralSettings|null
     */
    public function getGeneralSettings(): ?GeneralSettings
    {
        return $this->repository->getGeneralSettings();
    }
}
