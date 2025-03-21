<?php

namespace Adyen\Core\BusinessLogic\CheckoutAPI\PartialPayment\Request;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\ShopperReference;
use Adyen\Core\BusinessLogic\Domain\Checkout\Processors\PaymentRequest\PaymentRequestProcessor;

/**
 * Class StartPartialTransactionsRequest
 *
 * Request object to create partial Adyen payment transactions from shop checkout session using the /payments Web API endpoint
 *
 * @package Adyen\Core\BusinessLogic\CheckoutAPI\PaymentRequest\Request
 */
class StartPartialTransactionsRequest
{
    /**
     * @var string
     */
    private $paymentMethodType;
    /**
     * @var float
     */
    private $orderTotalAmount;
    /**
     * @var string
     */
    private $currency;
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
    private $paymentMethodStateData;
    /**
     * @var array
     */
    private $giftCardsStateData;
    /**
     * @var array
     */
    private $sessionData;
    /**
     * @var ShopperReference|null
     */
    private $shopperReference;

    /**
     * PaymentRequest constructor.
     *
     * @param string $reference
     * @param string $currency
     * @param string $returnUrl
     * @param float $orderTotalAmount
     * @param string $paymentMethodType Selected Adyen payment method type for witch payment request should be made
     * @param array $giftCardsData
     * @param array $additionalData
     * @param array $checkoutSession Arbitrary data that integration can set for usage in
     * individual @param ShopperReference|null $shopperReference
     * @see PaymentRequestProcessor instances for payment data transformation. Typically used for unpersisted
     * integration checkout payment request data that is needed for @see PaymentRequestProcessor instances
     */
    public function __construct(
        string            $reference,
        string            $currency,
        string            $returnUrl,
        float             $orderTotalAmount,
        string            $paymentMethodType,
        array             $giftCardsData = [],
        array             $additionalData = [],
        array $checkoutSession = [],
        ?ShopperReference $shopperReference = null
    ) {
        $this->paymentMethodType = $paymentMethodType;
        $this->orderTotalAmount = $orderTotalAmount;
        $this->currency = $currency;
        $this->reference = $reference;
        $this->returnUrl = $returnUrl;
        $this->giftCardsStateData = $giftCardsData;
        $this->paymentMethodStateData = $additionalData;
        $this->sessionData = $checkoutSession;
        $this->shopperReference = $shopperReference;
    }

    /**
     * @return string
     */
    public function getPaymentMethodType(): string
    {
        return $this->paymentMethodType;
    }

    /**
     * @return float
     */
    public function getOrderTotalAmount(): float
    {
        return $this->orderTotalAmount;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
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
    public function getGiftCardsStateData(): array
    {
        return $this->giftCardsStateData;
    }

    /**
     * @return array
     */
    public function getPaymentMethodStateData(): array
    {
        return $this->paymentMethodStateData;
    }

    /**
     * @return array
     */
    public function getSessionData(): array
    {
        return $this->sessionData;
    }

    /**
     * @return ShopperReference|null
     */
    public function getShopperReference(): ?ShopperReference
    {
        return $this->shopperReference;
    }
}
