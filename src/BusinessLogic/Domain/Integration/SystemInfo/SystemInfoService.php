<?php

namespace Adyen\Core\BusinessLogic\Domain\Integration\SystemInfo;

use Adyen\Core\BusinessLogic\Domain\InfoSettings\Models\SystemInfo;

/**
 * Interface SystemInfoService
 *
 * @package Adyen\Core\BusinessLogic\Domain\Integration\SystemInfo
 */
interface SystemInfoService
{
    /**
     * Contains information about: system version. plugin version, main theme name,
     * Shop URL, admin URL, async process url, database name and database version
     *
     * @return SystemInfo
     */
    public function getSystemInfo(): SystemInfo;
}
