<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\Processors\PaymentRequest;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Factory\PaymentRequestBuilder;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PaymentMethodCode;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\StartTransactionRequestContext;
use Adyen\Core\BusinessLogic\Domain\Payment\Repositories\PaymentMethodConfigRepository;
use Adyen\Core\BusinessLogic\Domain\Payment\Services\PaymentService;
use Adyen\Core\Infrastructure\ServiceRegister;
use Exception;

/**
 * Class AuthorizationTypeProcessor.
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\Processors\PaymentRequest\StateDataProcessors
 */
class AuthorizationTypeProcessor implements PaymentRequestProcessor
{
    /**
     * @var PaymentMethodConfigRepository
     */
    private $methodConfigRepository;

    /**
     * @param PaymentMethodConfigRepository $methodConfigRepository
     */
    public function __construct(PaymentMethodConfigRepository $methodConfigRepository)
    {
        $this->methodConfigRepository = $methodConfigRepository;
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
        $configuredPaymentMethod = $this->methodConfigRepository->getPaymentMethodByCode(
            (string)$context->getPaymentMethodCode()
        );

        if (!$configuredPaymentMethod &&
            in_array(
                (string)$context->getPaymentMethodCode(),
                [(string)PaymentMethodCode::payWithGoogle(), (string)PaymentMethodCode::googlePay()],
                true
            )
        ) {
            /** @var PaymentService $paymentService */
            $paymentService = ServiceRegister::getService(PaymentService::class);
            $configuredPaymentMethod = $paymentService->getGooglePayMethod();
        }

        if (!$configuredPaymentMethod ||
            !$configuredPaymentMethod->getAuthorizationType()) {
            return;
        }

        $builder->setAuthorizationType($configuredPaymentMethod->getAuthorizationType());
    }
}
