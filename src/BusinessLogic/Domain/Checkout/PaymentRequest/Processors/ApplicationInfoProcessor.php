<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Factory\PaymentRequestBuilder;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\ApplicationInfo\ApplicationInfo;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\ApplicationInfo\ExternalPlatform;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\ApplicationInfo\MerchantApplication;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\StartTransactionRequestContext;
use Adyen\Core\BusinessLogic\Domain\Configuration\Configuration;

/**
 * Class ApplicationInfoProcessor
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors
 */
class ApplicationInfoProcessor implements PaymentRequestProcessor
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
}
