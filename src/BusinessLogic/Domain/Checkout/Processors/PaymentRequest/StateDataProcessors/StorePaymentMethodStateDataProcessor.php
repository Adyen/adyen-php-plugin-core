<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\Processors\PaymentRequest\StateDataProcessors;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Factory\PaymentRequestBuilder;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\StartTransactionRequestContext;
use Adyen\Core\BusinessLogic\Domain\Checkout\Processors\PaymentRequest\PaymentRequestProcessor;

/**
 * Class StorePaymentMethodStateDataProcessor
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors\StateDataProcessors
 */
class StorePaymentMethodStateDataProcessor implements PaymentRequestProcessor
{
    private const DEFAULT_RECURRING_PROCESSING_MODEL = 'CardOnFile';

    public function process(PaymentRequestBuilder $builder, StartTransactionRequestContext $context): void
    {
        $storePaymentMethod = $context->getStateData()->get('storePaymentMethod');

        if (empty($storePaymentMethod)) {
            return;
        }

        $builder->setStorePaymentMethod($storePaymentMethod);
        $builder->setRecurringProcessingModel(self::DEFAULT_RECURRING_PROCESSING_MODEL);
    }
}
