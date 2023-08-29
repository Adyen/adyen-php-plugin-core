<?php

namespace Adyen\Core\Tests\BusinessLogic\AdminAPI\Versions\MockComponents;

use Adyen\Core\BusinessLogic\Domain\Integration\Version\VersionService;
use Adyen\Core\BusinessLogic\Domain\Version\Models\VersionInfo;

class MockVersionService implements VersionService
{

    /**
     * @inheritDoc
     */
    public function getVersionInfo(): VersionInfo
    {
        return new VersionInfo('1.0.0', '2.0.0');
    }
}
