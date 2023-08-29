<?php

namespace Adyen\Core\BusinessLogic\Domain\Integration\Version;

use Adyen\Core\BusinessLogic\Domain\Version\Models\VersionInfo;

/**
 * Class VersionService
 *
 * @package Adyen\Core\BusinessLogic\Domain\Integration\Version
 */
interface VersionService
{
    /**
     * Retrieves plugin current and latest version.
     *
     * @return VersionInfo
     */
    public function getVersionInfo(): VersionInfo;
}
