<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\Processors\PaymentRequest;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Factory\PaymentRequestBuilder;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\StartTransactionRequestContext;

/**
 * Class ShopperReferenceProcessor
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\Processors\PaymentRequest
 */
class ShopperReferenceProcessor implements PaymentRequestProcessor
{
    /**
     * @param PaymentRequestBuilder $builder
     * @param StartTransactionRequestContext $context
     *
     * @return void
     */
    public function process(PaymentRequestBuilder $builder, StartTransactionRequestContext $context): void
    {
        if ($context->getShopperReference()) {
            $builder->setShopperReference($context->getShopperReference());
        }
    }
}
