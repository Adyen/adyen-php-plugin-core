<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\Processors;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Factory\PaymentLinkRequestBuilder;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Models\StartPaymentLinkRequestContext;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\MissingActiveApiConnectionData;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Factory\PaymentRequestBuilder;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\StartTransactionRequestContext;
use Adyen\Core\BusinessLogic\Domain\Checkout\Processors\PaymentLinkRequest\PaymentLinkRequestProcessor;
use Adyen\Core\BusinessLogic\Domain\Checkout\Processors\PaymentRequest\PaymentRequestProcessor;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use Adyen\Core\BusinessLogic\Domain\Connection\Repositories\ConnectionSettingsRepository;
use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class PaymentRequestStateDataProcessor
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors
 */
class MerchantIdProcessor implements PaymentRequestProcessor, PaymentLinkRequestProcessor
{
    /**
     * @var ConnectionSettingsRepository
     */
    private $connectionSettingsRepository;

    /**
     * @param ConnectionSettingsRepository $connectionSettingsRepository
     */
    public function __construct(ConnectionSettingsRepository $connectionSettingsRepository)
    {
        $this->connectionSettingsRepository = $connectionSettingsRepository;
    }

    /**
     * @throws MissingActiveApiConnectionData
     */
    public function process(PaymentRequestBuilder $builder, StartTransactionRequestContext $context): void
    {
        $connectionData = $this->getConnectionData();

        $builder->setMerchantId($connectionData->getMerchantId());
    }

    /**
     * @param PaymentLinkRequestBuilder $builder
     * @param StartPaymentLinkRequestContext $context
     *
     * @return void
     *
     * @throws MissingActiveApiConnectionData
     */
    public function processPaymentLink(
        PaymentLinkRequestBuilder $builder,
        StartPaymentLinkRequestContext $context
    ): void {
        $connectionData = $this->getConnectionData();

        $builder->setMerchantAccount($connectionData->getMerchantId());
    }

    /**
     * @return ConnectionData
     *
     * @throws MissingActiveApiConnectionData
     */
    private function getConnectionData(): ConnectionData {
        $connectionData = $this->connectionSettingsRepository->getActiveConnectionData();
        if (!$connectionData) {
            throw new MissingActiveApiConnectionData(
                new TranslatableLabel(
                    'Invalid merchant configuration, no active API connection data found.',
                    'checkout.invalidConfiguration'
                )
            );
        }

        return $connectionData;
    }
}
