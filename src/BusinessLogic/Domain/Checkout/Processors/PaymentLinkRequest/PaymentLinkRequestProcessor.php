<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\Processors\PaymentLinkRequest;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Factory\PaymentLinkRequestBuilder;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Models\StartPaymentLinkRequestContext;

/**
 * Interface PaymentLinkRequestProcessor
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\Processors\PaymentLinkRequest
 */
interface PaymentLinkRequestProcessor
{
    /**
     * @param PaymentLinkRequestBuilder $builder
     * @param StartPaymentLinkRequestContext $context
     *
     * @return void
     */
    public function processPaymentLink(PaymentLinkRequestBuilder $builder, StartPaymentLinkRequestContext $context): void;
}
