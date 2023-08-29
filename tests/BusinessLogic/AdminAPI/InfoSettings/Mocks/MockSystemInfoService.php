<?php

namespace Adyen\Core\Tests\BusinessLogic\AdminAPI\InfoSettings\Mocks;

use Adyen\Core\BusinessLogic\Domain\InfoSettings\Models\SystemInfo;
use Adyen\Core\BusinessLogic\Domain\Integration\SystemInfo\SystemInfoService;

/**
 * Class MockSystemInfoService
 *
 * @package Adyen\Core\Tests\BusinessLogic\AdminAPI\InfoSettings\Mocks
 */
class MockSystemInfoService implements SystemInfoService
{

    /**
     * @inheritDoc
     */
    public function getSystemInfo(): SystemInfo
    {
        return new SystemInfo('1', '1', 'MAIN_THEME', 'shop.test', 'admin.test', 'async', 'database', 'databaseV');
    }
}