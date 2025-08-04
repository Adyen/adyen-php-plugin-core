<?php

namespace Adyen\Core\Tests\BusinessLogic\Common\MockComponents;


use Adyen\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;

/**
 * Class MockConnectionService.
 *
 * @package Adyen\Core\Tests\BusinessLogic\Common\MockComponents
 */
class MockConnectionService extends ConnectionService
{
    /**
     * @return void
     */
    public function reRegisterWebhook(): void
    {
    }
}
