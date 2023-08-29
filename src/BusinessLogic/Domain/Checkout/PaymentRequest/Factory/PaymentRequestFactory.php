<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Factory;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PaymentRequest;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\StartTransactionRequestContext;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors\PaymentRequestProcessorsRegistry;

/**
 * Class Factory
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Factory
 */
class PaymentRequestFactory
{
    /**
     * @var PaymentRequestBuilder
     */
    private $builder;

    public function __construct()
    {
        $this->builder = new PaymentRequestBuilder();
    }

    public function crate(StartTransactionRequestContext $context): PaymentRequest
    {
        foreach (PaymentRequestProcessorsRegistry::getProcessors($context->getPaymentMethodCode()) as $processor) {
            $processor->process($this->builder, $context);
        }

        return $this->builder->build();
    }
}
