<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Factory;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\AdditionalData\AdditionalData;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\AuthenticationData;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\BillingAddress;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\BrowserInfo;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\DeliveryAddress;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Installments;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\LineItem;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PaymentRequest;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\RiskData;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\ShopperName;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\ShopperReference;

/**
 * Class PaymentRequestBuilder
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Factory
 */
class PaymentRequestBuilder
{
    private const DEFAULT_CHANNEL = 'Web';
    /**
     * @var Amount
     */
    private $amount;
    /**
     * @var array
     */
    private $paymentMethod = [];
    /**
     * @var string
     */
    private $merchantId = '';
    /**
     * @var string
     */
    private $reference = '';
    /**
     * @var string
     */
    private $returnUrl = '';
    /**
     * @var BrowserInfo
     */
    private $browserInfo;
    /**
     * @var BillingAddress
     */
    private $billingAddress;
    /**
     * @var DeliveryAddress
     */
    private $deliveryAddress;
    /**
     * @var RiskData
     */
    private $riskData;
    /**
     * @var ShopperName
     */
    private $shopperName;
    /**
     * @var string
     */
    private $dateOfBirth = '';
    /**
     * @var string
     */
    private $telephoneNumber = '';
    /**
     * @var string
     */
    private $shopperEmail = '';
    /**
     * @var string
     */
    private $countryCode = '';
    /**
     * @var string
     */
    private $socialSecurityNumber = '';
    /**
     * @var Installments
     */
    private $installments;
    /**
     * @var bool
     */
    private $storePaymentMethod = false;
    /**
     * @var string
     */
    private $conversionId = '';
    /**
     * @var ShopperReference
     */
    private $shopperReference;
    /**
     * @var string|null
     */
    private $recurringProcessingModel;
    /**
     * @var string|null
     */
    private $shopperInteraction;
    /**
     * @var int
     */
    private $captureDelayHours = -1;
    /**
     * @var string
     */
    private $channel = self::DEFAULT_CHANNEL;
    /**
     * @var string
     */
    private $origin = '';
    /**
     * @var string
     */
    private $shopperLocale = '';
    /**
     * @var LineItem[]
     */
    private $lineItems = [];
    /**
     * @var AdditionalData
     */
    private $additionalData;
    /**
     * @var AuthenticationData
     */
    private $authenticationData;
    /**
     * @var string
     */
    private $deviceFingerprint = '';
    /**
     * @var array
     */
    private $bankAccount = [];

    public function build(): PaymentRequest
    {
        return new PaymentRequest(
            $this->amount,
            $this->merchantId,
            $this->reference,
            $this->returnUrl,
            $this->paymentMethod,
            $this->browserInfo,
            $this->billingAddress,
            $this->deliveryAddress,
            $this->riskData,
            $this->shopperName,
            $this->dateOfBirth,
            $this->telephoneNumber,
            $this->shopperEmail,
            $this->countryCode,
            $this->socialSecurityNumber,
            $this->installments,
            $this->storePaymentMethod,
            $this->conversionId,
            $this->shopperReference,
            $this->recurringProcessingModel,
            $this->shopperInteraction,
            $this->shopperLocale,
            $this->captureDelayHours,
            $this->channel,
            $this->origin,
            $this->lineItems,
            $this->additionalData,
            $this->authenticationData,
            $this->deviceFingerprint,
            $this->bankAccount
        );
    }

    /**
     * @param Amount $amount
     */
    public function setAmount(Amount $amount): void
    {
        $this->amount = $amount;
    }

    /**
     * @param array $paymentMethod
     * @return void
     */
    public function setPaymentMethod(array $paymentMethod): void
    {
        $this->paymentMethod = $paymentMethod;
    }

    /**
     * @param string $merchantId
     * @return void
     */
    public function setMerchantId(string $merchantId): void
    {
        $this->merchantId = $merchantId;
    }

    /**
     * @param string $reference
     * @return void
     */
    public function setReference(string $reference): void
    {
        $this->reference = $reference;
    }

    /**
     * @param string $returnUrl
     * @return void
     */
    public function setReturnUrl(string $returnUrl): void
    {
        $this->returnUrl = $returnUrl;
    }

    /**
     * @param BrowserInfo $browserInfo
     * @return void
     */
    public function setBrowserInfo(BrowserInfo $browserInfo): void
    {
        $this->browserInfo = $browserInfo;
    }

    /**
     * @param BillingAddress $billingAddress
     * @return void
     */
    public function setBillingAddress(BillingAddress $billingAddress): void
    {
        $this->billingAddress = $billingAddress;
    }

    /**
     * @param DeliveryAddress $deliveryAddress
     * @return void
     */
    public function setDeliveryAddress(DeliveryAddress $deliveryAddress): void
    {
        $this->deliveryAddress = $deliveryAddress;
    }

    /**
     * @param RiskData $riskData
     * @return void
     */
    public function setRiskData(RiskData $riskData): void
    {
        $this->riskData = $riskData;
    }

    /**
     * @param ShopperName $shopperName
     * @return void
     */
    public function setShopperName(ShopperName $shopperName): void
    {
        $this->shopperName = $shopperName;
    }

    /**
     * @param string $dateOfBirth
     * @return void
     */
    public function setDateOfBirth(string $dateOfBirth): void
    {
        $this->dateOfBirth = $dateOfBirth;
    }

    /**
     * @param string $telephoneNumber
     * @return void
     */
    public function setTelephoneNumber(string $telephoneNumber): void
    {
        $this->telephoneNumber = $telephoneNumber;
    }

    /**
     * @param string $shopperEmail
     * @return void
     */
    public function setShopperEmail(string $shopperEmail): void
    {
        $this->shopperEmail = $shopperEmail;
    }

    /**
     * @param string $countryCode
     * @return void
     */
    public function setCountryCode(string $countryCode): void
    {
        $this->countryCode = $countryCode;
    }

    /**
     * @param string $socialSecurityNumber
     * @return void
     */
    public function setSocialSecurityNumber(string $socialSecurityNumber): void
    {
        $this->socialSecurityNumber = $socialSecurityNumber;
    }

    /**
     * @param Installments $installments
     * @return void
     */
    public function setInstallments(Installments $installments): void
    {
        $this->installments = $installments;
    }

    /**
     * @param bool $storePaymentMethod
     * @return void
     */
    public function setStorePaymentMethod(bool $storePaymentMethod): void
    {
        $this->storePaymentMethod = $storePaymentMethod;
    }

    /**
     * @param string $conversionId
     * @return void
     */
    public function setConversionId(string $conversionId): void
    {
        $this->conversionId = $conversionId;
    }

    /**
     * @param ShopperReference $shopperReference
     * @return void
     */
    public function setShopperReference(ShopperReference $shopperReference): void
    {
        $this->shopperReference = $shopperReference;
    }

    /**
     * @param string $recurringProcessingModel
     * @return void
     */
    public function setRecurringProcessingModel(string $recurringProcessingModel): void
    {
        $this->recurringProcessingModel = $recurringProcessingModel;
    }

    /**
     * @param string $shopperInteraction
     * @return void
     */
    public function setShopperInteraction(string $shopperInteraction): void
    {
        $this->shopperInteraction = $shopperInteraction;
    }

    /**
     * @param string $shopperLocale
     * @return void
     */
    public function setShopperLocale(string $shopperLocale): void
    {
        $this->shopperLocale = $shopperLocale;
    }

    /**
     * @param int $captureDelayHours
     * @return void
     */
    public function setCaptureDelayHours(int $captureDelayHours): void
    {
        $this->captureDelayHours = $captureDelayHours;
    }

    /**
     * @param string $channel
     */
    public function setChannel(string $channel): void
    {
        $this->channel = $channel;
    }

    /**
     * @param string $origin
     */
    public function setOrigin(string $origin): void
    {
        $this->origin = $origin;
    }

    /**
     * @param LineItem[] $lineItems
     * @return void
     */
    public function setLineItems(array $lineItems): void
    {
        $this->lineItems = $lineItems;
    }

    /**
     * @param AdditionalData $additionalData
     * @return void
     */
    public function setAdditionalData(AdditionalData $additionalData): void
    {
        $additionalData = new AdditionalData(
            $additionalData->getRiskData() ??
                ($this->additionalData ? $this->additionalData->getRiskData() : null),
            $additionalData->getEnhancedSchemeData() ??
                ($this->additionalData ? $this->additionalData->getEnhancedSchemeData() : null),
            $additionalData->getManualCapture() ??
                ($this->additionalData ? $this->additionalData->getManualCapture() : null)
        );

        $this->additionalData = $additionalData;
    }

    /**
     * @param AuthenticationData $authenticationData
     */
    public function setAuthenticationData(AuthenticationData $authenticationData): void
    {
        $this->authenticationData = $authenticationData;
    }

    /**
     * @param string $deviceFingerprint
     */
    public function setDeviceFingerprint(string $deviceFingerprint): void
    {
        $this->deviceFingerprint = $deviceFingerprint;
    }

    public function setBankAccount(array $bankAccount): void
    {
        $this->bankAccount = $bankAccount;
    }
}
