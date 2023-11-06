<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\Processors;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Factory\PaymentLinkRequestBuilder;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Models\PaymentLinkRequestContext;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Factory\PaymentRequestBuilder;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\ApplicationInfo\ApplicationInfo;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\ApplicationInfo\ExternalPlatform;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\ApplicationInfo\MerchantApplication;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\StartTransactionRequestContext;
use Adyen\Core\BusinessLogic\Domain\Checkout\Processors\PaymentLinkRequest\PaymentLinkRequestProcessor;
use Adyen\Core\BusinessLogic\Domain\Checkout\Processors\PaymentRequest\PaymentRequestProcessor;
use Adyen\Core\BusinessLogic\Domain\Configuration\Configuration;

/**
 * Class ApplicationInfoProcessor
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors
 */
class ApplicationInfoProcessor implements PaymentRequestProcessor, PaymentLinkRequestProcessor
{
    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @param Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @param PaymentRequestBuilder $builder
     * @param StartTransactionRequestContext $context
     *
     * @return void
     */
    public function process(PaymentRequestBuilder $builder, StartTransactionRequestContext $context): void
    {
        $builder->setApplicationInfo(
            new ApplicationInfo(
                new ExternalPlatform(
                    $this->configuration->getIntegrationName(),
                    $this->configuration->getIntegrationVersion()
                ),
                new MerchantApplication($this->configuration->getPluginName(), $this->configuration->getPluginVersion())
            )
        );
    }

    /**
     * @param PaymentLinkRequestBuilder $builder
     * @param PaymentLinkRequestContext $context
     *
     * @return void
     */
    public function processPaymentLink(PaymentLinkRequestBuilder $builder, PaymentLinkRequestContext $context): void
    {
        $builder->setApplicationInfo(
            new ApplicationInfo(
                new ExternalPlatform(
                    $this->configuration->getIntegrationName(),
                    $this->configuration->getIntegrationVersion()
                ),
                new MerchantApplication($this->configuration->getPluginName(), $this->configuration->getPluginVersion())
            )
        );
    }
}
