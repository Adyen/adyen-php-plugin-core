<?php

namespace Adyen\Core\Tests\BusinessLogic\Domain\AuthorizationAdjustment\Mocks;

use Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Models\AuthorizationAdjustmentRequest;
use Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Proxies\AuthorizationAdjustmentProxy;

/**
 * Class MockAdjustmentProxy.
 *
 * @package Adyen\Core\Tests\BusinessLogic\Domain\AuthorizationAdjustment\Mocks
 */
class MockAdjustmentProxy implements AuthorizationAdjustmentProxy
{
    /**
     * @var bool
     */
    private $success;

    public function __construct()
    {
        $this->success = true;
    }

    /**
     * @param bool $success
     *
     * @return void
     */
    public function setMockSuccess(bool $success): void
    {
        $this->success = $success;
    }

    /**
     * @param AuthorizationAdjustmentRequest $request
     *
     * @return bool
     */
    public function adjustPayment(AuthorizationAdjustmentRequest $request): bool
    {
        return $this->success;
    }
}
