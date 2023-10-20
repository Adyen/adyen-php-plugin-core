<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\Processors\PaymentLinkRequest;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Factory\PaymentLinkRequestBuilder;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Models\PaymentLinkRequestContext;

/**
 * Interface PaymentLinkRequestProcessor
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\Processors\PaymentLinkRequest
 */
interface PaymentLinkRequestProcessor
{
    /**
     * @param PaymentLinkRequestBuilder $builder
     * @param PaymentLinkRequestContext $context
     *
     * @return void
     */
    public function processPaymentLink(PaymentLinkRequestBuilder $builder, PaymentLinkRequestContext $context): void;
}
