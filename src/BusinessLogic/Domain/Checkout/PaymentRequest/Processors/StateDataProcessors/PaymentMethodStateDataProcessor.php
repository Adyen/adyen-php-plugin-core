<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors\StateDataProcessors;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Factory\PaymentRequestBuilder;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\StartTransactionRequestContext;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors\PaymentRequestProcessor;

/**
 * Class PaymentRequestStateDataProcessor
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors
 */
class PaymentMethodStateDataProcessor implements PaymentRequestProcessor
{
    private const DEFAULT_RECURRING_PROCESSING_MODEL = 'CardOnFile';
    private const DEFAULT_SHOPPER_INTERACTION = 'ContAuth';

    public function process(PaymentRequestBuilder $builder, StartTransactionRequestContext $context): void
    {
        $paymentMethod = $context->getStateData()->get(
            'paymentMethod',
            ['type' => (string)$context->getPaymentMethodCode()]
        );
        $builder->setPaymentMethod($paymentMethod);

        if (!empty($paymentMethod['storedPaymentMethodId'])) {
            $builder->setShopperInteraction(self::DEFAULT_SHOPPER_INTERACTION);
            $builder->setRecurringProcessingModel(self::DEFAULT_RECURRING_PROCESSING_MODEL);
        }
    }
}
