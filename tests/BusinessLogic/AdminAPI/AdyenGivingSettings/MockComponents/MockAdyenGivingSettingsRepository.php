<?php

namespace Adyen\Core\Tests\BusinessLogic\AdminAPI\AdyenGivingSettings\MockComponents;

use Adyen\Core\BusinessLogic\Domain\AdyenGivingSettings\Models\AdyenGivingSettings;
use Adyen\Core\BusinessLogic\Domain\AdyenGivingSettings\Repositories\AdyenGivingSettingsRepository;

/**
 * Class MockAdyenGivingSettingsRepository
 *
 * @package Adyen\Core\Tests\BusinessLogic\AdminAPI\AdyenGivingSettings\MockComponents
 */
class MockAdyenGivingSettingsRepository implements AdyenGivingSettingsRepository
{
    /**
     * @var AdyenGivingSettings
     */
    private $adyenGivingSettings;

    /**
     * @inheritDoc
     */
    public function getAdyenGivingSettings(): ?AdyenGivingSettings
    {
        return $this->adyenGivingSettings;
    }

    /**
     * @inheritDoc
     */
    public function setAdyenGivingSettings(AdyenGivingSettings $adyenGivingSettings): void
    {
        $this->adyenGivingSettings = $adyenGivingSettings;
    }

    public function deleteAdyenGivingSettings(): void
    {
    }
}
