<?php

namespace Adyen\Core\Tests\BusinessLogic\CheckoutAPI\PaymentRequest\MockComponents;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Factory\PaymentRequestBuilder;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\StartTransactionRequestContext;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors\PaymentRequestProcessor;

/**
 * Class MockPaymentProcessor
 *
 * @package Adyen\Core\Tests\BusinessLogic\CheckoutAPI\PaymentRequest\MockComponents
 */
class MockPaymentRequestProcessor implements PaymentRequestProcessor
{
    /**
     * @var array
     */
    private $mockResponse;

    public function __construct(array $mockResponse = [])
    {
        $this->mockResponse = $mockResponse;
    }

    public function process(PaymentRequestBuilder $builder, StartTransactionRequestContext $context): void
    {
        $builder->setPaymentMethod([
            'mockData' => $this->mockResponse,
        ]);
    }
}
