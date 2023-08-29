<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models;

use Adyen\Core\BusinessLogic\Domain\Payment\Models\PaymentMethod;

/**
 * Class PaymentCheckoutConfigResult
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models
 */
class PaymentCheckoutConfigResult
{
    /**
     * @var string
     */
    private $connectionMode;
    /**
     * @var string
     */
    private $clientKey;
    /**
     * @var AvailablePaymentMethodsResponse
     */
    private $availablePaymentMethodsResponse;
    /**
     * @var PaymentMethod[]
     */
    private $paymentMethodsConfiguration;

    /**
     * PaymentCheckoutConfigResult constructor.
     *
     * @param string $connectionMode The environment mode (either test or live)
     * @param string $clientKey The generated client key form the plugin configuration
     * @param AvailablePaymentMethodsResponse $availablePaymentMethodsResponse
     * @param PaymentMethod[] $paymentMethodsConfiguration Configurations for each supported payment method on the checkout
     */
    public function __construct(
        string $connectionMode,
        string $clientKey,
        AvailablePaymentMethodsResponse $availablePaymentMethodsResponse,
        array $paymentMethodsConfiguration = []
    ) {
        $this->connectionMode = $connectionMode;
        $this->clientKey = $clientKey;
        $this->paymentMethodsConfiguration = $paymentMethodsConfiguration;
        $this->availablePaymentMethodsResponse = $availablePaymentMethodsResponse;
    }

    /**
     * @return string
     */
    public function getConnectionMode(): string
    {
        return $this->connectionMode;
    }

    /**
     * @return string
     */
    public function getClientKey(): string
    {
        return $this->clientKey;
    }

    /**
     * @return AvailablePaymentMethodsResponse
     */
    public function getAvailablePaymentMethodsResponse(): AvailablePaymentMethodsResponse
    {
        return $this->availablePaymentMethodsResponse;
    }

    /**
     * @return PaymentMethod[]
     */
    public function getPaymentMethodsConfiguration(): array
    {
        return $this->paymentMethodsConfiguration;
    }
}
