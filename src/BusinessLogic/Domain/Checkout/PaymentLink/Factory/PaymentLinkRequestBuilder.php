<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Factory;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Models\PaymentLinkRequest;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\ApplicationInfo\ApplicationInfo;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\BillingAddress;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\DeliveryAddress;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\LineItem;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\ShopperName;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\ShopperReference;

/**
 * Class PaymentLinkRequestBuilder.
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Factory
 */
class PaymentLinkRequestBuilder
{
    /**
     * @var string
     */
    private $reference = '';

    /**
     * @var string
     */
    private $merchantAccount = '';

    /**
     * @var Amount
     */
    private $amount;

    /**
     * @var array
     */
    private $allowedPaymentMethods = [];

    /**
     * @var string
     */
    private $countryCode = '';

    /**
     * @var ShopperReference
     */
    private $shopperReference;

    /**
     * @var string
     */
    private $shopperEmail = '';

    /**
     * @var string
     */
    private $shopperLocale = '';

    /**
     * @var BillingAddress
     */
    private $billingAddress;

    /**
     * @var DeliveryAddress
     */
    private $deliveryAddress;

    /**
     * @var int
     */
    private $captureDelayHours = -1;

    /**
     * @var bool
     */
    private $manualCapture = false;

    /**
     * @var ShopperName
     */
    private $shopperName;

    /**
     * @var string
     */
    private $dateOfBirth = '';

    /**
     * @var ApplicationInfo
     */
    private $applicationInfo;

    /**
     * @var LineItem[]
     */
    private $lineItems;

    /**
     * @var string
     */
    private $expiresAt;

    /**
     * @return PaymentLinkRequest
     */
    public function build(): PaymentLinkRequest
    {
        return new PaymentLinkRequest(
            $this->reference,
            $this->merchantAccount,
            $this->amount,
            $this->allowedPaymentMethods,
            $this->countryCode,
            $this->shopperReference,
            $this->shopperEmail,
            $this->shopperLocale,
            $this->billingAddress,
            $this->deliveryAddress,
            $this->captureDelayHours,
            $this->manualCapture,
            $this->shopperName,
            $this->dateOfBirth,
            $this->applicationInfo,
            $this->lineItems,
            $this->expiresAt
        );
    }

    /**
     * @param string $reference
     *
     * @return void
     */
    public function setReference(string $reference): void
    {
        $this->reference = $reference;
    }

    /**
     * @param string $merchantAccount
     *
     * @return void
     */
    public function setMerchantAccount(string $merchantAccount): void
    {
        $this->merchantAccount = $merchantAccount;
    }

    /**
     * @param Amount $amount
     *
     * @return void
     */
    public function setAmount(Amount $amount): void
    {
        $this->amount = $amount;
    }

    /**
     * @param array $allowedPaymentMethods
     *
     * @return void
     */
    public function setAllowedPaymentMethods(array $allowedPaymentMethods): void
    {
        $this->allowedPaymentMethods = $allowedPaymentMethods;
    }

    /**
     * @param string $countryCode
     *
     * @return void
     */
    public function setCountryCode(string $countryCode): void
    {
        $this->countryCode = $countryCode;
    }

    /**
     * @param ShopperReference $shopperReference
     *
     * @return void
     */
    public function setShopperReference(ShopperReference $shopperReference): void
    {
        $this->shopperReference = $shopperReference;
    }

    /**
     * @param string $shopperEmail
     *
     * @return void
     */
    public function setShopperEmail(string $shopperEmail): void
    {
        $this->shopperEmail = $shopperEmail;
    }

    /**
     * @param string $shopperLocale
     *
     * @return void
     */
    public function setShopperLocale(string $shopperLocale): void
    {
        $this->shopperLocale = $shopperLocale;
    }

    /**
     * @param BillingAddress $billingAddress
     *
     * @return void
     */
    public function setBillingAddress(BillingAddress $billingAddress): void
    {
        $this->billingAddress = $billingAddress;
    }

    /**
     * @param DeliveryAddress $deliveryAddress
     *
     * @return void
     */
    public function setDeliveryAddress(DeliveryAddress $deliveryAddress): void
    {
        $this->deliveryAddress = $deliveryAddress;
    }

    /**
     * @param int $captureDelayHours
     *
     * @return void
     */
    public function setCaptureDelayHours(int $captureDelayHours): void
    {
        $this->captureDelayHours = $captureDelayHours;
    }

    /**
     * @param bool $manualCapture
     *
     * @return void
     */
    public function setManualCapture(bool $manualCapture): void
    {
        $this->manualCapture = $manualCapture;
    }

    /**
     * @param ShopperName $shopperName
     *
     * @return void
     */
    public function setShopperName(ShopperName $shopperName): void
    {
        $this->shopperName = $shopperName;
    }

    /**
     * @param string $dateOfBirth
     *
     * @return void
     */
    public function setDateOfBirth(string $dateOfBirth): void
    {
        $this->dateOfBirth = $dateOfBirth;
    }

    /**
     * @param ApplicationInfo $applicationInfo
     *
     * @return void
     */
    public function setApplicationInfo(ApplicationInfo $applicationInfo): void
    {
        $this->applicationInfo = $applicationInfo;
    }

    /**
     * @param LineItem[] $lineItems
     *
     * @return void
     */
    public function setLineItems(array $lineItems): void
    {
        $this->lineItems = $lineItems;
    }

    /**
     * @param string $expiresAt
     *
     * @return void
     */
    public function setExpiresAt(string $expiresAt): void
    {
        $this->expiresAt = $expiresAt;
    }
}
