<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\Processors\PaymentRequest\StateDataProcessors;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Factory\PaymentRequestBuilder;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\StartTransactionRequestContext;
use Adyen\Core\BusinessLogic\Domain\Checkout\Processors\PaymentRequest\PaymentRequestProcessor;
use Adyen\Core\BusinessLogic\Domain\Payment\Repositories\PaymentMethodConfigRepository;
use Exception;

/**
 * Class RecurringPaymentProcessor
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\Processors\PaymentRequest
 */
class RecurringPaymentProcessor implements PaymentRequestProcessor
{

    /**
     * @var PaymentMethodConfigRepository
     */
    private $methodConfigRepository;

    /**
     * String representation of shopper interaction when paying the first time.
     */
    private const SHOPPER_INTERACTION = 'Ecommerce';

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
        $paymentMethod = $context->getStateData()->get(
            'paymentMethod',
            ['type' => (string)$context->getPaymentMethodCode()]
        );

        $configuredPaymentMethod = $this->methodConfigRepository->getPaymentMethodByCode(
            (string)$context->getPaymentMethodCode()
        );

        if (isset($paymentMethod['storedPaymentMethodId']) ||
            !$configuredPaymentMethod ||
            !$configuredPaymentMethod->getSupportsRecurringPayments() ||
            !$configuredPaymentMethod->getEnableTokenization()) {
            return;
        }

        $builder->setShopperInteraction(self::SHOPPER_INTERACTION);
        $builder->setRecurringProcessingModel($configuredPaymentMethod->getTokenType()->getType());
        $builder->setStorePaymentMethod(true);
    }
}
