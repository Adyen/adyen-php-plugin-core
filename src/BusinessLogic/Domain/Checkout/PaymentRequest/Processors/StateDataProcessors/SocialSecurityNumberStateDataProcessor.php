<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors\StateDataProcessors;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Factory\PaymentRequestBuilder;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\StartTransactionRequestContext;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors\PaymentRequestProcessor;

/**
 * Class SocialSecurityNumberStateDataProcessor
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors\StateDataProcessors
 */
class SocialSecurityNumberStateDataProcessor implements PaymentRequestProcessor
{
    public function process(PaymentRequestBuilder $builder, StartTransactionRequestContext $context): void
    {
        $socialSecurityNumber = $context->getStateData()->get('socialSecurityNumber');

        if (empty($socialSecurityNumber)) {
            return;
        }

        $builder->setSocialSecurityNumber($socialSecurityNumber);
    }
}
