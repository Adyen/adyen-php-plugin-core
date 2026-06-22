<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\Processors;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Factory\PaymentLinkRequestBuilder;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Models\PaymentLinkRequestContext;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Factory\PaymentRequestBuilder;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\AdditionalData\AdditionalData;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\StartTransactionRequestContext;
use Adyen\Core\BusinessLogic\Domain\Checkout\Processors\PaymentLinkRequest\PaymentLinkRequestProcessor;
use Adyen\Core\BusinessLogic\Domain\Checkout\Processors\PaymentRequest\PaymentRequestProcessor;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Models\CaptureType;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Services\GeneralSettingsService;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\AuthorizationType;
use Adyen\Core\BusinessLogic\Domain\Payment\Services\PaymentService;
use Exception;

/**
 * Class CaptureProcessor
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors
 */
class CaptureProcessor implements PaymentRequestProcessor, PaymentLinkRequestProcessor
{
    /**
     * @var GeneralSettingsService
     */
    private $generalSettingsService;

    /**
     * @var PaymentService
     */
    private $paymentService;

    /**
     * @param GeneralSettingsService $generalSettingsService
     * @param PaymentService $paymentService
     */
    public function __construct(
        GeneralSettingsService $generalSettingsService,
        PaymentService $paymentService
    ) {
        $this->generalSettingsService = $generalSettingsService;
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
        $generalSettings = $this->generalSettingsService->getGeneralSettings();
        $configuredPaymentMethod = $this->paymentService->getPaymentMethodByCodeWithFallback(
            (string)$context->getPaymentMethodCode()
        );

        if ((!$generalSettings || $generalSettings->getCapture()->getType() !== CaptureType::MANUAL) &&
            (
                !$configuredPaymentMethod ||
                !$configuredPaymentMethod->getAuthorizationType() ||
                !$configuredPaymentMethod->getAuthorizationType()->equal(AuthorizationType::preAuthorization())
            )
        ) {
            return;
        }

        $builder->setAdditionalData(new AdditionalData(null, null, true));
    }

    /**
     * @param PaymentLinkRequestBuilder $builder
     * @param PaymentLinkRequestContext $context
     *
     * @return void
     */
    public function processPaymentLink(
        PaymentLinkRequestBuilder $builder,
        PaymentLinkRequestContext $context
    ): void {
        $generalSettings = $this->generalSettingsService->getGeneralSettings();

        if (!$generalSettings || $generalSettings->getCapture()->getType() !== CaptureType::MANUAL) {
            return;
        }

        $builder->setManualCapture(true);
    }
}
