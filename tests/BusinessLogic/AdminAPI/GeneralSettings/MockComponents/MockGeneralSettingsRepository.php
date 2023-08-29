<?php

namespace Adyen\Core\Tests\BusinessLogic\AdminAPI\GeneralSettings\MockComponents;

use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Models\GeneralSettings;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Repositories\GeneralSettingsRepository;

/**
 * Class MockGeneralSettingsRepository
 *
 * @package Adyen\Core\Tests\BusinessLogic\AdminAPI\GeneralSettings\MockComponents
 */
class MockGeneralSettingsRepository implements GeneralSettingsRepository
{
    /**
     * @var GeneralSettings
     */
    private $generalSettings;

    /**
     * @inheritDoc
     */
    public function getGeneralSettings(): ?GeneralSettings
    {
        return $this->generalSettings;
    }

    /**
     * @inheritDoc
     */
    public function setGeneralSettings(GeneralSettings $generalSettings): void
    {
    }

    /**
     * @param GeneralSettings $generalSettings
     *
     * @return void
     */
    public function setMockGeneralSettings(GeneralSettings $generalSettings): void
    {
        $this->generalSettings = $generalSettings;
    }

    public function deleteGeneralSettings(): void
    {
    }
}
