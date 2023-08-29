<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors\StateDataProcessors;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Factory\PaymentRequestBuilder;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\BillingAddress;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\StartTransactionRequestContext;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors\PaymentRequestProcessor;

/**
 * Class BillingAddressStateDataProcessor
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors\StateDataProcessors
 */
class BillingAddressStateDataProcessor implements PaymentRequestProcessor
{
    public function process(PaymentRequestBuilder $builder, StartTransactionRequestContext $context): void
    {
        $billingAddressRawData = $context->getStateData()->get('billingAddress');

        if (empty($billingAddressRawData)) {
            return;
        }

        $country = $billingAddressRawData['country'] ?? '';

        $billingAddress = new BillingAddress(
            $billingAddressRawData['city'] ?? '',
            $country,
            $billingAddressRawData['houseNumberOnName'] ?? '',
            $billingAddressRawData['postalCode'] ?? '',
            $billingAddressRawData['stateOrProvince'] ?? $country,
            $billingAddressRawData['street'] ?? ''
        );

        $builder->setBillingAddress($billingAddress);
    }
}
