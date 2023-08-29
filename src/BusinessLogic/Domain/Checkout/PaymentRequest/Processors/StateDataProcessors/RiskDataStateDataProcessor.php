<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors\StateDataProcessors;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Factory\PaymentRequestBuilder;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\RiskData;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\StartTransactionRequestContext;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors\PaymentRequestProcessor;

/**
 * Class RiskDataStateDataProcessor
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors\StateDataProcessors
 */
class RiskDataStateDataProcessor implements PaymentRequestProcessor
{
    public function process(PaymentRequestBuilder $builder, StartTransactionRequestContext $context): void
    {
        $rawRiskData = $context->getStateData()->get('riskData');

        if (empty($rawRiskData)) {
            return;
        }

        $riskData = new RiskData(
            $rawRiskData['clientData'] ?? '',
            $rawRiskData['customFields'] ?? [],
            $rawRiskData['fraudOffset'] ?? 0,
            $rawRiskData['profileReference'] ?? ''
        );

        $builder->setRiskData($riskData);
    }
}
