<?php

namespace Adyen\Core\Tests\BusinessLogic\Domain\Capture\Mocks;

use Adyen\Core\BusinessLogic\Domain\Capture\Models\CaptureRequest;
use Adyen\Core\BusinessLogic\Domain\Capture\Proxies\CaptureProxy;

/**
 * Class MockCaptureProxy
 *
 * @package Adyen\Core\Tests\BusinessLogic\Domain\Capture\Mocks
 */
class MockCaptureProxy implements CaptureProxy
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
     * @inheritDoc
     */
    public function capturePayment(CaptureRequest $request): bool
    {
        return $this->success;
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
}
