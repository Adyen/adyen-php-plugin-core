<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\Processors\PaymentLinkRequest;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Factory\PaymentLinkRequestBuilder;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Models\PaymentLinkRequestContext;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Services\GeneralSettingsService;
use Adyen\Core\Infrastructure\Utility\TimeProvider;
use DateInterval;
use DateTimeInterface;
use Exception;

/**
 * Class ExpiresAtProcessor.
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\Processors\PaymentLinkRequest
 */
class ExpiresAtProcessor implements PaymentLinkRequestProcessor
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

    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    public function processPaymentLink(
        PaymentLinkRequestBuilder $builder,
        PaymentLinkRequestContext $context
    ): void {
        if ($context->getExpiresAt() !== null) {
            $builder->setExpiresAt($context->getExpiresAt()->format(DateTimeInterface::ATOM));

            return;
        }

        $generalSettings = $this->generalSettingsService->getGeneralSettings();

        if (!$generalSettings) {
            return;
        }

        $expirationDelayDays = $generalSettings->getDefaultLinkExpirationTime();
        $date = TimeProvider::getInstance()->getCurrentLocalTime()->add(
            new DateInterval('P' . $expirationDelayDays . 'D')
        );

        $builder->setExpiresAt($date->format(DateTimeInterface::ATOM));
    }
}
