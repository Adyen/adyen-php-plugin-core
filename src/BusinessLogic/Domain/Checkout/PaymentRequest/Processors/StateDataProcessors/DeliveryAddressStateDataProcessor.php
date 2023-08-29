<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors\StateDataProcessors;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Factory\PaymentRequestBuilder;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\DeliveryAddress;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\StartTransactionRequestContext;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors\PaymentRequestProcessor;

/**
 * Class DeliveryAddressStateDataProcessor
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors\StateDataProcessors
 */
class DeliveryAddressStateDataProcessor implements PaymentRequestProcessor
{
    public function process(PaymentRequestBuilder $builder, StartTransactionRequestContext $context): void
    {
        $deliveryAddressRawData = $context->getStateData()->get('deliveryAddress');

        if (empty($deliveryAddressRawData)) {
            return;
        }

        $country = $deliveryAddressRawData['country'] ?? '';

        $deliveryAddress = new DeliveryAddress(
            $deliveryAddressRawData['city'] ?? '',
            $country,
            $deliveryAddressRawData['houseNumberOnName'] ?? '',
            $deliveryAddressRawData['postalCode'] ?? '',
            $deliveryAddressRawData['stateOrProvince'] ?? $country,
            $deliveryAddressRawData['street'] ?? ''
        );

        $builder->setDeliveryAddress($deliveryAddress);
    }
}
