<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\Processors\PaymentRequest;

use Adyen\Core\BusinessLogic\DataAccess\Payment\Repositories\PaymentMethodConfigRepository;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\MissingActiveApiConnectionData;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Factory\PaymentRequestBuilder;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PaymentMethodResponse;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\ShopperReference;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\StartTransactionRequestContext;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Proxies\StoredDetailsProxy;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use Adyen\Core\BusinessLogic\Domain\Connection\Repositories\ConnectionSettingsRepository;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\PaymentMethod;
use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Exception;

/**
 * Class RecurringPaymentProcessor
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\Processors\PaymentRequest
 */
class RecurringPaymentProcessor implements PaymentRequestProcessor
{
    /**
     * @var StoredDetailsProxy $storedDetailsProxy
     */
    private $storedDetailsProxy;

    /**
     * @var ConnectionSettingsRepository
     */
    private $connectionSettingsRepository;

    /**
     * @var PaymentMethodConfigRepository
     */
    private $methodConfigRepository;

    /**
     * @param StoredDetailsProxy $storedDetailsProxy
     * @param ConnectionSettingsRepository $connectionSettingsRepository
     * @param PaymentMethodConfigRepository $methodConfigRepository
     */
    public function __construct(
        StoredDetailsProxy $storedDetailsProxy,
        ConnectionSettingsRepository $connectionSettingsRepository,
        PaymentMethodConfigRepository $methodConfigRepository
    ) {
        $this->storedDetailsProxy = $storedDetailsProxy;
        $this->connectionSettingsRepository = $connectionSettingsRepository;
        $this->methodConfigRepository = $methodConfigRepository;
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
        $paymentMethod = $this->methodConfigRepository->getPaymentMethodByCode(
            (string)$context->getPaymentMethodCode()
        );

        if (!$paymentMethod ||
            !$paymentMethod->getSupportsRecurringPayments() ||
            !$paymentMethod->getEnableTokenization()) {
            return;
        }

        $connectionData = $this->getConnectionData();
        $storedPaymentMethods = $this->storedDetailsProxy->getStoredPaymentDetails(
            ShopperReference::parse('12121'),
            $connectionData->getMerchantId()
        );

        if (!$this->isPaymentStored($paymentMethod, $storedPaymentMethods)) {
            return;
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
     *
     * @return bool
     */
    private function isPaymentStored(PaymentMethod $method, array $storedMethods): bool
    {
        foreach ($storedMethods as $storedMethod) {
            if ($method->getCode() === $storedMethod->getType()) {
                return true;
            }
        }

        return false;
    }
}
