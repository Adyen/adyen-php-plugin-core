<?php

namespace Adyen\Core\BusinessLogic\Domain\Cancel\Proxies;

use Adyen\Core\BusinessLogic\Domain\Cancel\Models\CancelRequest;

/**
 * Interface CancelProxy
 *
 * @package Adyen\Core\BusinessLogic\Domain\Cancel\Proxies
 */
interface CancelProxy
{
    /**
     * Makes cancel request. Returns true if capture succeeded, otherwise false.
     *
     * @param CancelRequest $request
     *
     * @return bool
     */
    public function cancelPayment(CancelRequest $request): bool;
}
