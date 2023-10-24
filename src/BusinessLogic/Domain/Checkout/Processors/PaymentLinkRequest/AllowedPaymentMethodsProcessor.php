<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\Processors\PaymentLinkRequest;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Factory\PaymentLinkRequestBuilder;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Models\PaymentLinkRequestContext;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PaymentMethodCode;
use Adyen\Core\BusinessLogic\Domain\Checkout\Processors\PaymentLinkRequest\Exceptions\NoSupportedPaymentMethods;
use Adyen\Core\BusinessLogic\Domain\Payment\Exceptions\FailedToRetrievePaymentMethodsException;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\PaymentMethod;
use Adyen\Core\BusinessLogic\Domain\Payment\Services\PaymentService;
use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class AllowedPaymentMethodsProcessor
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\Processors\PaymentLinkRequest
 */
class AllowedPaymentMethodsProcessor implements PaymentLinkRequestProcessor
{
    /**
     * @var PaymentService
     */
    private $paymentService;

    /**
     * @param PaymentService $paymentService
     */
    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * @inheritDoc
     *
     * @throws FailedToRetrievePaymentMethodsException
     * @throws NoSupportedPaymentMethods
     */
    public function processPaymentLink(
        PaymentLinkRequestBuilder $builder,
        PaymentLinkRequestContext $context
    ): void {
        $allowedPaymentMethods = [];
        $currency = $context->getAmount()->getCurrency()->getIsoCode();

        $configuredPaymentMethods = $this->paymentService->getConfiguredMethods();
        foreach ($configuredPaymentMethods as $configuredPaymentMethod) {
            if ($configuredPaymentMethod->getExcludeFromPayByLink() ||
                !$configuredPaymentMethod->getSupportsPaymentLink() ||
                (!in_array('ANY', $configuredPaymentMethod->getCurrencies()) &&
                    !in_array($currency, $configuredPaymentMethod->getCurrencies()))) {

                continue;
            }

            if (PaymentMethodCode::isOneyMethod($configuredPaymentMethod->getCode())) {
                $allowedPaymentMethods = array_merge(
                    $allowedPaymentMethods,
                    $this->getOneyCodes($configuredPaymentMethod)
                );

                continue;
            }

            $allowedPaymentMethods[] = $configuredPaymentMethod->getCode();
        }

        if (empty($allowedPaymentMethods)) {
            throw new NoSupportedPaymentMethods(
                new TranslatableLabel(
                    'There are no payment methods that supports payment link request.',
                    'paymentLink.noMethods'
                )
            );
        }

        $builder->setAllowedPaymentMethods($allowedPaymentMethods);
    }

    /**
     * @param PaymentMethod $paymentMethod
     *
     * @return array
     */
    private function getOneyCodes(PaymentMethod $paymentMethod): array
    {
        $oneyInstallments = [];

        foreach ($paymentMethod->getAdditionalData()->getSupportedInstallments() as $installment) {
            $oneyInstallments[] = 'facilypay_' . $installment . 'x';
        }

        return $oneyInstallments;
    }
}
