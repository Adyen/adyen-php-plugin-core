<?php

namespace Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Proxies;

use Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Models\AuthorizationAdjustmentRequest;

/**
 * Interface AuthorizationAdjustmentProxy.
 *
 * @package Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Proxies
 */
interface AuthorizationAdjustmentProxy
{
    /**
     * Makes authorization adjustment request. Returns true if request succeeded, otherwise false.
     *
     * @param AuthorizationAdjustmentRequest $request
     *
     * @return bool
     */
    public function adjustPayment(AuthorizationAdjustmentRequest $request): bool;
}
