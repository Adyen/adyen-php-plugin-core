<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Factory\PaymentRequestBuilder;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\AdditionalData\AdditionalData;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\StartTransactionRequestContext;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Models\CaptureType;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Services\GeneralSettingsService;

/**
 * Class CaptureProcessor
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors
 */
class CaptureProcessor implements PaymentRequestProcessor
{
    /**
     * @var GeneralSettingsService
     */
    private $generalSettingsService;

    /**
     * @param GeneralSettingsService $generalSettingsService
     */
    public function __construct(GeneralSettingsService $generalSettingsService)
    {
        $this->generalSettingsService = $generalSettingsService;
    }

    public function process(PaymentRequestBuilder $builder, StartTransactionRequestContext $context): void
    {
        $generalSettings = $this->generalSettingsService->getGeneralSettings();

        if (!$generalSettings || $generalSettings->getCapture()->getType() !== CaptureType::MANUAL) {
            return;
        }

        $builder->setAdditionalData(new AdditionalData(null, null, true));
    }
}
