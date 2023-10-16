<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Proxies;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Models\PaymentLink;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Models\PaymentLinkRequest;

/**
 * Class PaymentLinkProxy
 *
 * @package Adyen\Core\BusinessLogic\Domain\PaymentLink\Proxies
 */
interface PaymentLinkProxy
{
    /**
     * @param PaymentLinkRequest $request
     *
     * @return PaymentLink
     */
    public function createPaymentLink(PaymentLinkRequest $request): PaymentLink;
}
