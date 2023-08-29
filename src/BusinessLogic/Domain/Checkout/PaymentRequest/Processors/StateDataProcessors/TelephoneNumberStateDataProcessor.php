<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors\StateDataProcessors;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Factory\PaymentRequestBuilder;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\StartTransactionRequestContext;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors\PaymentRequestProcessor;

/**
 * Class TelephoneNumberStateDataProcessor
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors\StateDataProcessors
 */
class TelephoneNumberStateDataProcessor implements PaymentRequestProcessor
{
    public function process(PaymentRequestBuilder $builder, StartTransactionRequestContext $context): void
    {
        $phone = $context->getStateData()->get('telephoneNumber');

        if (empty($phone)) {
            return;
        }

        $builder->setTelephoneNumber($phone);
    }
}
