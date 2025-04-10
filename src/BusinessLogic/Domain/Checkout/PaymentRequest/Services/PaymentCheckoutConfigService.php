<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Services;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\MissingActiveApiConnectionData;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\MissingClientKeyConfiguration;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\AvailablePaymentMethodsResponse;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Country;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PaymentCheckoutConfigResult;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PaymentMethodResponse;
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
     * @param bool $isGuest
     *
     * @return PaymentCheckoutConfigResult
     *
     * @throws MissingActiveApiConnectionData
     * @throws MissingClientKeyConfiguration
     */
    public function getExpressPaymentCheckoutConfig(
        Amount $amount,
        Country $country = null,
        string $shopperLocale = 'en-US',
        ?ShopperReference $shopperReference = null,
        bool $isGuest = false
    ): PaymentCheckoutConfigResult {
        return $this->getPaymentCheckoutConfigForConfiguredMethods(
            $this->paymentMethodConfigRepository->getEnabledExpressCheckoutPaymentMethods($isGuest),
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
     * @throws Exception
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

        $paymentMethodsResponse = $this->paymentsProxy->getAvailablePaymentMethods(
            new PaymentMethodsRequest(
                $connectionSettings->getActiveConnectionData()->getMerchantId(),
                array_map(static function (PaymentMethod $paymentMethod) {
                    return $paymentMethod->getCode();
                }, $paymentMethodsConfiguration),
                $amount,
                $country,
                $shopperLocale,
                $shopperReference
            )
        );

        $methodsResponse = [];

        if (!empty($_SERVER['HTTP_USER_AGENT'])) {
            $userAgent = $_SERVER['HTTP_USER_AGENT'];

            foreach ($paymentMethodsResponse->getPaymentMethodsResponse() as $methodResponse) {
                if ($methodResponse->getType() === 'applepay' &&
                    (!strpos($userAgent, 'Safari') || strpos($userAgent, 'Chrome'))) {
                    continue;
                }

                if ($methodResponse->getType() === 'googlepay') {
                    $metadata = $methodResponse->getMetadata();
                    if (isset($metadata['type'])) {
                        $metadata['type'] = 'paywithgoogle';
                    }

                    $methodsResponse[] = new PaymentMethodResponse($methodResponse->getName(), 'paywithgoogle', $metadata);
                }

                $methodsResponse[] = $methodResponse;
            }

            $paymentMethodsResponse = new AvailablePaymentMethodsResponse(
                $methodsResponse,
                $paymentMethodsResponse->getStoredPaymentMethodsResponse()
            );
        }

        if ($shopperReference) {
            $recurringPaymentMethods = $this->storedDetailsProxy->getStoredPaymentDetails(
                $shopperReference,
                $connectionSettings->getActiveConnectionData()->getMerchantId()
            );

            $paymentMethodsResponse = new AvailablePaymentMethodsResponse(
                $methodsResponse ?: $paymentMethodsResponse->getPaymentMethodsResponse(),
                $paymentMethodsResponse->getStoredPaymentMethodsResponse(),
                $this->filterRecurringPaymentMethods(
                    $recurringPaymentMethods,
                    $paymentMethodsResponse->getPaymentMethodsResponse(),
                    $paymentMethodsConfiguration
                )
            );
        }

        return new PaymentCheckoutConfigResult(
            $connectionSettings->getMode(),
            $clientKey,
            $paymentMethodsResponse,
            $paymentMethodsConfiguration
        );
    }

    /**
     * @param PaymentMethodResponse[] $recurringPaymentMethods
     * @param PaymentMethodResponse[] $availablePaymentMethods
     * @param PaymentMethod[] $paymentMethodsConfiguration
     *
     * @return PaymentMethodResponse[]
     */
    private function filterRecurringPaymentMethods(
        array $recurringPaymentMethods,
        array $availablePaymentMethods,
        array $paymentMethodsConfiguration
    ): array {
        $paymentMethodsMap = [];

        foreach ($availablePaymentMethods as $paymentMethod) {
            $paymentMethodsMap[$paymentMethod->getType()] = $paymentMethod;
        }

        return array_values(
            array_filter(
                $this->filterDisabledTokenizationPaymentMethods($recurringPaymentMethods, $paymentMethodsConfiguration),
                static function ($paymentMethodResponse) use ($paymentMethodsMap) {
                    return isset($paymentMethodsMap[$paymentMethodResponse->getType()]);
                }
            )
        );
    }

    /**
     * Return only payment methods that have enabled tokenization in payment method config.
     *
     * @param PaymentMethodResponse[] $recurringPaymentMethods
     * @param PaymentMethod[] $paymentMethodsConfiguration
     *
     * @return array
     */
    private function filterDisabledTokenizationPaymentMethods(
        array $recurringPaymentMethods,
        array $paymentMethodsConfiguration
    ): array {
        return array_filter(
            $recurringPaymentMethods,
            function ($recurringPaymentMethod) use ($paymentMethodsConfiguration) {
                foreach ($paymentMethodsConfiguration as $configuredPaymentMethod) {
                    if ($configuredPaymentMethod->getCode() === $recurringPaymentMethod->getType() &&
                        $configuredPaymentMethod->getEnableTokenization()) {
                        return true;
                    }
                }
                return false;
            }
        );
    }
}
