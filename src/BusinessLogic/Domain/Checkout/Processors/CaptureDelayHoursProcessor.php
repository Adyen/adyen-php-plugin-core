<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\Processors;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Factory\PaymentLinkRequestBuilder;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Models\PaymentLinkRequestContext;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Factory\PaymentRequestBuilder;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\StartTransactionRequestContext;
use Adyen\Core\BusinessLogic\Domain\Checkout\Processors\PaymentLinkRequest\PaymentLinkRequestProcessor;
use Adyen\Core\BusinessLogic\Domain\Checkout\Processors\PaymentRequest\PaymentRequestProcessor;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Models\CaptureType;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Services\GeneralSettingsService;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\AuthorizationType;
use Adyen\Core\BusinessLogic\Domain\Payment\Repositories\PaymentMethodConfigRepository;
use Exception;

/**
 * Class CaptureDelayHoursProcessor
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors
 */
class CaptureDelayHoursProcessor implements PaymentRequestProcessor, PaymentLinkRequestProcessor
{
    /**
     * @var GeneralSettingsService
     */
    private $generalSettingsService;

    /**
     * @var PaymentMethodConfigRepository
     */
    private $methodConfigRepository;

    /**
     * @param GeneralSettingsService $generalSettingsService
     * @param PaymentMethodConfigRepository $methodConfigRepository
     */
    public function __construct(
        GeneralSettingsService $generalSettingsService,
        PaymentMethodConfigRepository $methodConfigRepository
    ) {
        $this->generalSettingsService = $generalSettingsService;
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
        $generalSettings = $this->generalSettingsService->getGeneralSettings();
        $configuredPaymentMethod = $this->methodConfigRepository->getPaymentMethodByCode(
            (string)$context->getPaymentMethodCode()
        );

        if (!$generalSettings) {
            $builder->setCaptureDelayHours(0);

            return;
        }

        if (!$generalSettings->getCapture()->equal(CaptureType::manual()) &&
            (
                !$configuredPaymentMethod->getAuthorizationType() ||
                !$configuredPaymentMethod->getAuthorizationType()->equal(AuthorizationType::preAuthorization())
            )
        ) {
            $builder->setCaptureDelayHours($generalSettings->getCaptureDelayHours());
        }
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

        if (!$generalSettings) {
            $builder->setCaptureDelayHours(0);

            return;
        }

        if (!$generalSettings->getCapture()->equal(CaptureType::manual())) {
            $builder->setCaptureDelayHours($generalSettings->getCaptureDelayHours());
        }
    }
}
