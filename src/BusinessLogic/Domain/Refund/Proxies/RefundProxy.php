<?php

namespace Adyen\Core\BusinessLogic\Domain\Refund\Proxies;

use Adyen\Core\BusinessLogic\Domain\Refund\Models\RefundRequest;

/**
 * Interface RefundProxy
 *
 * @package Adyen\Core\BusinessLogic\Domain\Refund\Proxies
 */
interface RefundProxy
{
    /**
     * Makes refund request. Returns true if capture succeeded, otherwise false.
     *
     * @param RefundRequest $request
     *
     * @return bool
     */
    public function refundPayment(RefundRequest $request): bool;
}
