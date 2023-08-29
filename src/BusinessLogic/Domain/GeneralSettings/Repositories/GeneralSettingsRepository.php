<?php

namespace Adyen\Core\BusinessLogic\Domain\GeneralSettings\Repositories;

use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Models\GeneralSettings;
use Exception;

/**
 * Class GeneralSettingsRepository
 *
 * @package Adyen\Core\BusinessLogic\Domain\GeneralSettings\Repositories
 */
interface GeneralSettingsRepository
{
    /**
     * Returns GeneralSettings instance for current store context.
     *
     * @return GeneralSettings|null
     */
    public function getGeneralSettings(): ?GeneralSettings;

    /**
     * Insert/update GeneralSettings for current store context;
     *
     * @param GeneralSettings $generalSettings
     *
     * @return void
     */
    public function setGeneralSettings(GeneralSettings $generalSettings): void;

    /**
     * Deletes general settings.
     *
     * @return void
     *
     * @throws Exception
     */
    public function deleteGeneralSettings(): void;
}
