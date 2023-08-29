<?php

namespace Adyen\Core\BusinessLogic\Domain\Capture\Proxies;

use Adyen\Core\BusinessLogic\Domain\Capture\Models\CaptureRequest;

/**
 * Interface CaptureProxy
 *
 * @package Adyen\Core\BusinessLogic\Domain\Capture\Proxies
 */
interface CaptureProxy
{
    /**
     * Makes capture request. Returns true if capture succeeded, otherwise false.
     *
     * @param CaptureRequest $request
     *
     * @return bool
     */
    public function capturePayment(CaptureRequest $request): bool;
}
