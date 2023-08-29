<?php

namespace Adyen\Core\BusinessLogic\CheckoutAPI\PaymentRequest\Request;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;

/**
 * Class PaymentRequest
 *
 * Request object to create Adyen payment transaction from shop checkout session using the /payments Web API endpoint
 *
 * @package Adyen\Core\BusinessLogic\CheckoutAPI\PaymentRequest\Request
 */
class StartTransactionRequest
{
    /**
     * @var string
     */
    private $paymentMethodType;
    /**
     * @var Amount
     */
    private $amount;
    /**
     * @var string
     */
    private $reference;
    /**
     * @var string
     */
    private $returnUrl;
    /**
     * @var array
     */
    private $stateData;
    /**
     * @var array
     */
    private $sessionData;

    /**
     * PaymentRequest constructor.
     *
     * @param string $paymentMethodType Selected Adyen payment method type for witch payment request should be made
     * @param array $stateData The state.data from the onChange or onSubmit event from Adyen WebComponent.
     * @param array $checkoutSession Arbitrary data that integration can set for usage in
     * individual @see PaymentRequestProcessor instances for payment data transformation. Typically used for unpersisted
     * integration checkout payment request data that is needed for @see PaymentRequestProcessor instances
     */
    public function __construct(
        string $paymentMethodType,
        Amount $amount,
        string $reference,
        string $returnUrl,
        array $stateData,
        array $checkoutSession = []
    ) {
        $this->paymentMethodType = $paymentMethodType;
        $this->amount = $amount;
        $this->reference = $reference;
        $this->returnUrl = $returnUrl;
        $this->stateData = $stateData;
        $this->sessionData = $checkoutSession;
    }

    /**
     * @return string
     */
    public function getPaymentMethodType(): string
    {
        return $this->paymentMethodType;
    }

    /**
     * @return Amount
     */
    public function getAmount(): Amount
    {
        return $this->amount;
    }

    /**
     * @return string
     */
    public function getReference(): string
    {
        return $this->reference;
    }

    /**
     * @return string
     */
    public function getReturnUrl(): string
    {
        return $this->returnUrl;
    }

    /**
     * @return array
     */
    public function getStateData(): array
    {
        return $this->stateData;
    }

    /**
     * @return array
     */
    public function getSessionData(): array
    {
        return $this->sessionData;
    }
}
