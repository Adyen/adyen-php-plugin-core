<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;

/**
 * Class StartTransactionRequestContext
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models
 */
class StartTransactionRequestContext
{
    /**
     * @var PaymentMethodCode
     */
    private $paymentMethodCode;
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
     * @var DataBag
     */
    private $checkoutSession;
    /**
     * @var DataBag
     */
    private $stateData;

    public function __construct(
        PaymentMethodCode $paymentMethodType,
        Amount            $amount,
        string            $reference,
        string            $returnUrl,
        DataBag           $stateData,
        DataBag           $checkoutSession
    ) {
        $this->paymentMethodCode = $paymentMethodType;
        $this->amount = $amount;
        $this->reference = $reference;
        $this->returnUrl = $returnUrl;
        $this->checkoutSession = $checkoutSession;
        $this->stateData = $stateData;
    }

    /**
     * @return PaymentMethodCode
     */
    public function getPaymentMethodCode(): PaymentMethodCode
    {
        return $this->paymentMethodCode;
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
     * @return DataBag
     */
    public function getStateData(): DataBag
    {
        return $this->stateData;
    }

    /**
     * @return DataBag
     */
    public function getCheckoutSession(): DataBag
    {
        return $this->checkoutSession;
    }
}
