<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Factory;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Models\PaymentLinkRequest;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Models\PaymentLinkRequestContext;
use Adyen\Core\BusinessLogic\Domain\Checkout\Processors\PaymentLinkRequest\PaymentLinkRequestProcessorsRegistry;

/**
 * Class PaymentLinkRequestFactory.
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Factory
 */
class PaymentLinkRequestFactory
{
    /**
     * @var PaymentLinkRequestBuilder
     */
    private $builder;

    public function __construct()
    {
        $this->builder = new PaymentLinkRequestBuilder();
    }

    /**
     * @param PaymentLinkRequestContext $context
     *
     * @return PaymentLinkRequest
     */
    public function create(PaymentLinkRequestContext $context): PaymentLinkRequest
    {
        foreach (PaymentLinkRequestProcessorsRegistry::getProcessors() as $processor) {
            $processor->processPaymentLink($this->builder, $context);
        }

        return $this->builder->build();
    }
}
