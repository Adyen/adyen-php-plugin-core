<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\Processors\PaymentRequest\StateDataProcessors;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Factory\PaymentRequestBuilder;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Installments;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\StartTransactionRequestContext;
use Adyen\Core\BusinessLogic\Domain\Checkout\Processors\PaymentRequest\PaymentRequestProcessor;

/**
 * Class InstallmentsStateDataProcessor
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors\StateDataProcessors
 */
class InstallmentsStateDataProcessor implements PaymentRequestProcessor
{
    public function process(PaymentRequestBuilder $builder, StartTransactionRequestContext $context): void
    {
        $rawInstallmentsData = $context->getStateData()->get('installments');

        if (empty($rawInstallmentsData)) {
            return;
        }

        $installments = new Installments(
            $rawInstallmentsData['value'] ?? 1,
            $rawInstallmentsData['plan'] ?? 'regular'
        );

        $builder->setInstallments($installments);
    }
}
