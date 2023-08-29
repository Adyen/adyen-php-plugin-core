<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Factory\PaymentRequestBuilder;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\StartTransactionRequestContext;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Models\CaptureType;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Services\GeneralSettingsService;

/**
 * Class CaptureDelayHoursProcessor
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors
 */
class CaptureDelayHoursProcessor implements PaymentRequestProcessor
{
    /**
     * @var GeneralSettingsService
     */
    protected $generalSettingsService;

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

        if (!$generalSettings) {
            $builder->setCaptureDelayHours(0);

            return;
        }

        if (!$generalSettings->getCapture()->equal(CaptureType::manual())) {
            $builder->setCaptureDelayHours($generalSettings->getCaptureDelayHours());
        }
    }
}
