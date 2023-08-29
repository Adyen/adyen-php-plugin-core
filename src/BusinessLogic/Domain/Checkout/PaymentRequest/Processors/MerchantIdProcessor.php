<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\MissingActiveApiConnectionData;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Factory\PaymentRequestBuilder;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\StartTransactionRequestContext;
use Adyen\Core\BusinessLogic\Domain\Connection\Repositories\ConnectionSettingsRepository;
use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class PaymentRequestStateDataProcessor
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors
 */
class MerchantIdProcessor implements PaymentRequestProcessor
{
    /**
     * @var ConnectionSettingsRepository
     */
    private $connectionSettingsRepository;

    public function __construct(ConnectionSettingsRepository $connectionSettingsRepository)
    {
        $this->connectionSettingsRepository = $connectionSettingsRepository;
    }

    /**
     * @throws MissingActiveApiConnectionData
     */
    public function process(PaymentRequestBuilder $builder, StartTransactionRequestContext $context): void
    {
        $connectionData = $this->connectionSettingsRepository->getActiveConnectionData();
        if (!$connectionData) {
            throw new MissingActiveApiConnectionData(
                new TranslatableLabel(
                    'Invalid merchant configuration, no active API connection data found.',
                    'checkout.invalidConfiguration'
                )
            );
        }

        $builder->setMerchantId($connectionData->getMerchantId());
    }
}
