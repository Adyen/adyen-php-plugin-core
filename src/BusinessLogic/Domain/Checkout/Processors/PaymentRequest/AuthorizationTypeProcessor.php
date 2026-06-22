<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\Processors\PaymentRequest;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Factory\PaymentRequestBuilder;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\StartTransactionRequestContext;
use Adyen\Core\BusinessLogic\Domain\Payment\Services\PaymentService;
use Exception;

/**
 * Class AuthorizationTypeProcessor.
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\Processors\PaymentRequest\StateDataProcessors
 */
class AuthorizationTypeProcessor implements PaymentRequestProcessor
{
    /**
     * @var PaymentService
     */
    private $paymentService;

    /**
     * @param PaymentService  $paymentService
     */
    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * @param PaymentRequestBuilder $builder
     * @param StartTransactionRequestContext $context
     *
     * @return void
     *
     * @throws Exception
     */
    public function process(PaymentRequestBuilder $builder, StartTransactionRequestContext $context): void
    {
        $configuredPaymentMethod = $this->paymentService->getPaymentMethodByCodeWithFallback(
            (string)$context->getPaymentMethodCode()
        );

        if (!$configuredPaymentMethod ||
            !$configuredPaymentMethod->getAuthorizationType()) {
            return;
        }

        $builder->setAuthorizationType($configuredPaymentMethod->getAuthorizationType());
    }
}
