<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\Processors\PaymentRequest\StateDataProcessors;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\MissingActiveApiConnectionData;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Factory\PaymentRequestBuilder;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PaymentMethodCode;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\StartTransactionRequestContext;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Proxies\StoredDetailsProxy;
use Adyen\Core\BusinessLogic\Domain\Checkout\Processors\PaymentRequest\PaymentRequestProcessor;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use Adyen\Core\BusinessLogic\Domain\Connection\Repositories\ConnectionSettingsRepository;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\PaymentMethod;
use Adyen\Core\BusinessLogic\Domain\Payment\Repositories\PaymentMethodConfigRepository;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PaymentMethodResponse;
use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Exception;

/**
 * Class PaymentRequestStateDataProcessor
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors
 */
class PaymentMethodStateDataProcessor implements PaymentRequestProcessor
{
    private const DEFAULT_RECURRING_PROCESSING_MODEL = 'CardOnFile';
    private const DEFAULT_SHOPPER_INTERACTION = 'ContAuth';
    private const SEPA_RECURRING = ['bcmc_mobile', 'eps', 'giropay', 'ideal', 'directEbanking'];
    private const SEPA = 'sepadirectdebit';

    /**
     * @var PaymentMethodConfigRepository
     */
    private $methodConfigRepository;

    /**
     * @var ConnectionSettingsRepository
     */
    private $connectionSettingsRepository;

    /**
     * @var StoredDetailsProxy
     */
    private $detailsProxy;

    /**
     * @param PaymentMethodConfigRepository $methodConfigRepository
     * @param ConnectionSettingsRepository $connectionSettingsRepository
     * @param StoredDetailsProxy $detailsProxy
     */
    public function __construct(
        PaymentMethodConfigRepository $methodConfigRepository,
        ConnectionSettingsRepository $connectionSettingsRepository,
        StoredDetailsProxy $detailsProxy
    ) {
        $this->methodConfigRepository = $methodConfigRepository;
        $this->connectionSettingsRepository = $connectionSettingsRepository;
        $this->detailsProxy = $detailsProxy;
    }

    /**
     * @param PaymentRequestBuilder $builder
     * @param StartTransactionRequestContext $context
     *
     * @return void
     * @throws Exception
     */
    public function process(PaymentRequestBuilder $builder, StartTransactionRequestContext $context): void
    {
        $paymentMethod = $context->getStateData()->get(
            'paymentMethod',
            ['type' => (string)$context->getPaymentMethodCode()]
        );

        if (empty($paymentMethod['storedPaymentMethodId'])) {
            $builder->setPaymentMethod($paymentMethod);

            return;
        }

        if ($context->getPaymentMethodCode()->equals(PaymentMethodCode::scheme())) {
            $builder->setShopperInteraction(self::DEFAULT_SHOPPER_INTERACTION);
            $builder->setRecurringProcessingModel(self::DEFAULT_RECURRING_PROCESSING_MODEL);
            $builder->setPaymentMethod($paymentMethod);

            return;
        }

        $configuredPaymentMethod = $this->methodConfigRepository->getPaymentMethodByCode(
            (string)$context->getPaymentMethodCode()
        );

        if (!$configuredPaymentMethod ||
            !$configuredPaymentMethod->getSupportsRecurringPayments() ||
            !$configuredPaymentMethod->getEnableTokenization() ||
            !$context->getShopperReference()) {

            return;
        }

        $storedDetails = $this->detailsProxy->getStoredPaymentDetails(
            $context->getShopperReference(),
            $this->getConnectionData()->getMerchantId()
        );

        if ($this->isPaymentStored($configuredPaymentMethod, $storedDetails, $paymentMethod['storedPaymentMethodId'])) {
            $builder->setShopperInteraction(self::DEFAULT_SHOPPER_INTERACTION);
            $builder->setRecurringProcessingModel($configuredPaymentMethod->getTokenType()->getType());
            $this->setPaymentMethod($builder, $paymentMethod);
        }
    }

    /**
     * @return ConnectionData
     *
     * @throws MissingActiveApiConnectionData
     */
    private function getConnectionData(): ConnectionData
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

        return $connectionData;
    }

    /**
     * @param PaymentMethod $method
     * @param PaymentMethodResponse[] $storedMethods
     * @param string $storedId
     *
     * @return bool
     */
    private function isPaymentStored(PaymentMethod $method, array $storedMethods, string $storedId): bool
    {
        foreach ($storedMethods as $storedMethod) {
            if ($method->getCode() === $storedMethod->getType() && $storedId === $storedMethod->getMetaData(
                )['RecurringDetail']['recurringDetailReference']) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param PaymentRequestBuilder $builder
     * @param array $paymentMethod
     *
     * @return void
     */
    private function setPaymentMethod(PaymentRequestBuilder $builder, array $paymentMethod): void
    {
        if (in_array($paymentMethod['type'], self::SEPA_RECURRING)) {
            $paymentMethod['type'] = self::SEPA;
        }

        $builder->setPaymentMethod($paymentMethod);
    }
}
