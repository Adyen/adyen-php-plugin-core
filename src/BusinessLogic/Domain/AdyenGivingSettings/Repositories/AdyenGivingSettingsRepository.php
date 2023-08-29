<?php

namespace Adyen\Core\BusinessLogic\Domain\AdyenGivingSettings\Repositories;

use Adyen\Core\BusinessLogic\Domain\AdyenGivingSettings\Models\AdyenGivingSettings;
use Exception;

/**
 * Interface AdyenGivingSettingsRepository
 *
 * @package Adyen\Core\BusinessLogic\Domain\AdyenGivingSettings\Repositories
 */
interface AdyenGivingSettingsRepository
{
    /**
     * Returns AdyenGivingSettings instance for current store context.
     *
     * @return AdyenGivingSettings|null
     */
    public function getAdyenGivingSettings(): ?AdyenGivingSettings;

    /**
     * Insert/update AdyenGivingSettings for current store context;
     *
     * @param AdyenGivingSettings $adyenGivingSettings
     *
     * @return void
     */
    public function setAdyenGivingSettings(AdyenGivingSettings $adyenGivingSettings): void;

    /**
     * Deletes AdyenGivingSettings.
     *
     * @return void
     *
     * @throws Exception
     */
    public function deleteAdyenGivingSettings(): void;
}
