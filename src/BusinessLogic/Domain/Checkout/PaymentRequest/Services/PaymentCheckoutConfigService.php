<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Services;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\MissingActiveApiConnectionData;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\MissingClientKeyConfiguration;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\AvailablePaymentMethodsResponse;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Country;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PaymentCheckoutConfigResult;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PaymentMethodsRequest;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\ShopperReference;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Proxies\PaymentsProxy;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Proxies\StoredDetailsProxy;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFountException;
use Adyen\Core\BusinessLogic\Domain\Connection\Repositories\ConnectionSettingsRepository;
use Adyen\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\PaymentMethod;
use Adyen\Core\BusinessLogic\Domain\Payment\Repositories\PaymentMethodConfigRepository;
use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Exception;

/**
 * Class PaymentCheckoutConfigService
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Services
 */
class PaymentCheckoutConfigService
{
    /**
     * @var ConnectionSettingsRepository
     */
    private $connectionSettingsRepository;
    /**
     * @var PaymentMethodConfigRepository
     */
    private $paymentMethodConfigRepository;
    /**
     * @var PaymentsProxy
     */
    private $paymentsProxy;
    /**
     * @var StoredDetailsProxy
     */
    private $storedDetailsProxy;
    /**
     * @var ConnectionService
     */
    private $connectionService;

    public function __construct(
        ConnectionSettingsRepository $connectionSettingsRepository,
        PaymentMethodConfigRepository $paymentMethodConfigRepository,
        PaymentsProxy $paymentsProxy,
        StoredDetailsProxy $storedDetailsProxy,
        ConnectionService $connectionService
    ) {
        $this->connectionSettingsRepository = $connectionSettingsRepository;
        $this->paymentMethodConfigRepository = $paymentMethodConfigRepository;
        $this->paymentsProxy = $paymentsProxy;
        $this->storedDetailsProxy = $storedDetailsProxy;
        $this->connectionService = $connectionService;
    }

    /**
     * Gets the payment checkout configuration for the configuration of the Adyen's web checkout instance
     *
     * @param Amount $amount
     * @param Country|null $country
     * @param string $shopperLocale
     * @param ShopperReference|null $shopperReference
     * @return PaymentCheckoutConfigResult
     *
     * @throws MissingActiveApiConnectionData
     * @throws MissingClientKeyConfiguration
     */
    public function getPaymentCheckoutConfig(
        Amount $amount,
        Country $country = null,
        string $shopperLocale = 'en-US',
        ?ShopperReference $shopperReference = null
    ): PaymentCheckoutConfigResult {
        return $this->getPaymentCheckoutConfigForConfiguredMethods(
            $this->paymentMethodConfigRepository->getConfiguredPaymentMethods(),
            $amount,
            $country,
            $shopperLocale,
            $shopperReference
        );
    }

    /**
     * Gets the payment checkout configuration for the configuration of the Adyen's web checkout instance for
     * express checkout with only express checkout payment methods in response.
     *
     * @param Amount $amount
     * @param Country|null $country
     * @param string $shopperLocale
     * @param ShopperReference|null $shopperReference
     * @return PaymentCheckoutConfigResult
     *
     * @throws MissingActiveApiConnectionData
     * @throws MissingClientKeyConfiguration
     */
    public function getExpressPaymentCheckoutConfig(
        Amount $amount,
        Country $country = null,
        string $shopperLocale = 'en-US',
        ?ShopperReference $shopperReference = null
    ): PaymentCheckoutConfigResult {
        return $this->getPaymentCheckoutConfigForConfiguredMethods(
            $this->paymentMethodConfigRepository->getEnabledExpressCheckoutPaymentMethods(),
            $amount,
            $country,
            $shopperLocale,
            $shopperReference
        );
    }

    /**
     * Disable stored payment details.
     *
     * @param ShopperReference $shopperReference
     * @param string $detailReference
     *
     * @return void
     *
     * @throws ConnectionSettingsNotFountException
     * @throws Exception
     */
    public function disableStoredPaymentDetails(ShopperReference $shopperReference, string $detailReference): void
    {
        $connectionSettings = $this->connectionService->getConnectionData();

        if (!$connectionSettings) {
            throw new ConnectionSettingsNotFountException(
                new TranslatableLabel('Connection settings not found.', 'connection.settingsNotFound')
            );
        }

        $merchantId = $connectionSettings->getActiveConnectionData()->getMerchantId();
        $this->storedDetailsProxy->disable($shopperReference, $detailReference, $merchantId);
    }

    /**
     * Gets the payment checkout configuration for the configuration of the Adyen's web checkout instance for
     * provided configured payment methods.
     *
     * @param PaymentMethod[] $paymentMethodsConfiguration
     * @param Amount $amount
     * @param Country|null $country
     * @param string $shopperLocale
     * @param ShopperReference|null $shopperReference
     * @return PaymentCheckoutConfigResult
     *
     * @throws MissingActiveApiConnectionData
     * @throws MissingClientKeyConfiguration
     */
    protected function getPaymentCheckoutConfigForConfiguredMethods(
        array $paymentMethodsConfiguration,
        Amount $amount,
        Country $country = null,
        string $shopperLocale = 'en-US',
        ?ShopperReference $shopperReference = null
    ): PaymentCheckoutConfigResult {
        $connectionSettings = $this->connectionSettingsRepository->getConnectionSettings();
        if (!$connectionSettings) {
            throw new MissingActiveApiConnectionData(
                new TranslatableLabel(
                    'Invalid merchant configuration, no active API connection data found.',
                    'checkout.invalidConfiguration'
                )
            );
        }

        $clientKey = $connectionSettings->getActiveConnectionData()->getClientKey();
        if (!$clientKey) {
            throw new MissingClientKeyConfiguration(
                new TranslatableLabel(
                    'Invalid configuration, no client key configuration found.',
                    'checkout.noClientKey'
                )
            );
        }

        $paymentMethodsResponse = $this->paymentsProxy->getAvailablePaymentMethods(new PaymentMethodsRequest(
            $connectionSettings->getActiveConnectionData()->getMerchantId(),
            array_map(static function (PaymentMethod $paymentMethod) {
                return $paymentMethod->getCode();
            }, $paymentMethodsConfiguration),
            $amount,
            $country,
            $shopperLocale,
            $shopperReference
        ));

        if (!empty($_SERVER['HTTP_USER_AGENT'])) {
            $userAgent = $_SERVER['HTTP_USER_AGENT'];
            $methodsResponse = [];

            foreach ($paymentMethodsResponse->getPaymentMethodsResponse() as $methodResponse) {
                if ($methodResponse->getType() === 'applepay' &&
                    (!strpos($userAgent, 'Safari') || strpos($userAgent, 'Chrome'))) {
                    continue;
                }

                $methodsResponse[] = $methodResponse;
            }

            $paymentMethodsResponse = new AvailablePaymentMethodsResponse(
                $methodsResponse,
                $paymentMethodsResponse->getStoredPaymentMethodsResponse()
            );
        }

        return new PaymentCheckoutConfigResult(
            $connectionSettings->getMode(),
            $clientKey,
            $paymentMethodsResponse,
            $paymentMethodsConfiguration
        );
    }
}
