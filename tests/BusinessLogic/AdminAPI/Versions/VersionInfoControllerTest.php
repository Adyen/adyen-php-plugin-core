<?php

namespace Adyen\Core\Tests\BusinessLogic\AdminAPI\Versions;

use Adyen\Core\BusinessLogic\AdminAPI\AdminAPI;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;

class VersionInfoControllerTest extends BaseTestCase
{
    public function testGetVersionInfo()
    {
        // act
        $result = AdminAPI::get()->versions()->getVersionInfo();

        // assert
        self::assertEquals(['installed' => '1.0.0', 'latest' => '2.0.0'], $result->toArray());
    }
}
