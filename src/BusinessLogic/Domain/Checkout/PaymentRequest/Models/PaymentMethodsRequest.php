<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;

/**
 * Class PaymentMethodsRequest
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models
 */
class PaymentMethodsRequest
{
    /**
     * @var string
     */
    private $merchantId;
    /**
     * @var string[]
     */
    private $allowedPaymentMethods;
    /**
     * @var Amount|null
     */
    private $amount;
    /**
     * @var Country|null
     */
    private $country;
    /**
     * @var string|null
     */
    private $shopperLocale;
    /**
     * @var ShopperReference|null
     */
    private $shopperReference;

    /**
     * @param string $merchantId
     * @param string[] $allowedPaymentMethods
     */
    public function __construct(
        string $merchantId,
        array $allowedPaymentMethods,
        Amount $amount = null,
        Country $country = null,
        string $shopperLocale = null,
        ?ShopperReference $shopperReference = null
    ) {
        $this->merchantId = $merchantId;
        $this->allowedPaymentMethods = array_values(PaymentMethodCode::getExtendedCodesList($allowedPaymentMethods));
        $this->amount = $amount;
        $this->country = $country;
        $this->shopperLocale = $shopperLocale;
        $this->shopperReference = $shopperReference;
    }

    /**
     * @return string
     */
    public function getMerchantId(): string
    {
        return $this->merchantId;
    }

    /**
     * @return string[]
     */
    public function getAllowedPaymentMethods(): array
    {
        return $this->allowedPaymentMethods;
    }

    /**
     * @return Amount|null
     */
    public function getAmount(): ?Amount
    {
        return $this->amount;
    }

    /**
     * @return Country|null
     */
    public function getCountry(): ?Country
    {
        return $this->country;
    }

    /**
     * @return string|null
     */
    public function getShopperLocale(): ?string
    {
        return $this->shopperLocale;
    }

    /**
     * @return ShopperReference|null
     */
    public function getShopperReference(): ?ShopperReference
    {
        return $this->shopperReference;
    }
}
