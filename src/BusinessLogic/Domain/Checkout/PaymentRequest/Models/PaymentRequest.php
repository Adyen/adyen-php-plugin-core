<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\AdditionalData\AdditionalData;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;

/**
 * Class PaymentRequest
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models
 */
class PaymentRequest
{
    /**
     * @var Amount
     */
    private $amount;
    /**
     * @var string
     */
    private $merchantId;
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
    private $paymentMethod;
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
    private $dateOfBirth;
    /**
     * @var string
     */
    private $telephoneNumber;
    /**
     * @var string
     */
    private $shopperEmail;
    /**
     * @var string
     */
    private $countryCode;
    /**
     * @var string
     */
    private $socialSecurityNumber;
    /**
     * @var Installments
     */
    private $installments;
    /**
     * @var bool
     */
    private $storePaymentMethod;
    /**
     * @var string
     */
    private $conversionId;
    /**
     * @var ShopperReference|null
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
     * @var string
     */
    private $shopperLocale;
    /**
     * @var int
     */
    private $captureDelayHours;
    /**
     * @var string
     */
    private $channel;
    /**
     * @var string
     */
    private $origin;
    /**
     * @var LineItem[]
     */
    private $lineItems;
    /**
     * @var AdditionalData
     */
    private $additionalData;
    /**
     * @var AuthenticationData|null
     */
    private $authenticationData;
    /**
     * @var string
     */
    private $deviceFingerprint;
    /**
     * @var array
     */
    private $bankAccount;

    public function __construct(
        Amount $amount,
        string $merchantId,
        string $reference,
        string $returnUrl,
        array $paymentMethod,
        ?BrowserInfo $browserInfo = null,
        ?BillingAddress $billingAddress = null,
        ?DeliveryAddress $deliveryAddress = null,
        ?RiskData $riskData = null,
        ?ShopperName $shopperName = null,
        string $dateOfBirth = '',
        string $telephoneNumber = '',
        string $shopperEmail = '',
        string $countryCode = '',
        string $socialSecurityNumber = '',
        ?Installments $installments = null,
        bool $storePaymentMethod = false,
        string $conversionId = '',
        ?ShopperReference $shopperReference = null,
        string $recurringProcessingModel = null,
        string $shopperInteraction = null,
        string $shopperLocale = '',
        int $captureDelayHours = -1,
        string $channel = '',
        string $origin = '',
        array $lineItems = [],
        AdditionalData $additionalData = null,
        AuthenticationData $authenticationData = null,
        string $deviceFingerprint = '',
        array $bankAccount = []
    ) {
        $this->amount = $amount;
        $this->merchantId = $merchantId;
        $this->reference = $reference;
        $this->returnUrl = $returnUrl;
        $this->paymentMethod = $paymentMethod;
        $this->browserInfo = $browserInfo;
        $this->billingAddress = $billingAddress;
        $this->deliveryAddress = $deliveryAddress;
        $this->riskData = $riskData;
        $this->shopperName = $shopperName;
        $this->dateOfBirth = $dateOfBirth;
        $this->telephoneNumber = $telephoneNumber;
        $this->shopperEmail = $shopperEmail;
        $this->countryCode = $countryCode;
        $this->socialSecurityNumber = $socialSecurityNumber;
        $this->installments = $installments;
        $this->storePaymentMethod = $storePaymentMethod;
        $this->conversionId = $conversionId;
        $this->shopperReference = $shopperReference;
        $this->recurringProcessingModel = $recurringProcessingModel;
        $this->shopperInteraction = $shopperInteraction;
        $this->shopperLocale = $shopperLocale;
        $this->captureDelayHours = $captureDelayHours;
        $this->channel = $channel;
        $this->origin = $origin;
        $this->lineItems = $lineItems;
        $this->additionalData = $additionalData;
        $this->authenticationData = $authenticationData;
        $this->deviceFingerprint = $deviceFingerprint;
        $this->bankAccount = $bankAccount;
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
    public function getMerchantId(): string
    {
        return $this->merchantId;
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
    public function getPaymentMethod(): array
    {
        return $this->paymentMethod;
    }

    /**
     * @return BrowserInfo|null
     */
    public function getBrowserInfo(): ?BrowserInfo
    {
        return $this->browserInfo;
    }

    /**
     * @return BillingAddress|null
     */
    public function getBillingAddress(): ?BillingAddress
    {
        return $this->billingAddress;
    }

    /**
     * @return DeliveryAddress|null
     */
    public function getDeliveryAddress(): ?DeliveryAddress
    {
        return $this->deliveryAddress;
    }

    /**
     * @return RiskData|null
     */
    public function getRiskData(): ?RiskData
    {
        return $this->riskData;
    }

    /**
     * @return ShopperName|null
     */
    public function getShopperName(): ?ShopperName
    {
        return $this->shopperName;
    }

    /**
     * @return string
     */
    public function getDateOfBirth(): string
    {
        return $this->dateOfBirth;
    }

    /**
     * @return string
     */
    public function getTelephoneNumber(): string
    {
        return $this->telephoneNumber;
    }

    /**
     * @return string
     */
    public function getShopperEmail(): string
    {
        return $this->shopperEmail;
    }

    /**
     * @return string
     */
    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    /**
     * @return string
     */
    public function getSocialSecurityNumber(): string
    {
        return $this->socialSecurityNumber;
    }

    /**
     * @return Installments|null
     */
    public function getInstallments(): ?Installments
    {
        return $this->installments;
    }

    /**
     * @return bool
     */
    public function isStorePaymentMethod(): bool
    {
        return $this->storePaymentMethod;
    }

    /**
     * @return string
     */
    public function getConversionId(): string
    {
        return $this->conversionId;
    }

    /**
     * @return ?ShopperReference
     */
    public function getShopperReference(): ?ShopperReference
    {
        return $this->shopperReference;
    }

    /**
     * @return string|null
     */
    public function getRecurringProcessingModel(): ?string
    {
        return $this->recurringProcessingModel;
    }

    /**
     * @return string|null
     */
    public function getShopperInteraction(): ?string
    {
        return $this->shopperInteraction;
    }

    /**
     * @return string
     */
    public function getShopperLocale(): string
    {
        return $this->shopperLocale;
    }

    /**
     * @return int
     */
    public function getCaptureDelayHours(): int
    {
        return $this->captureDelayHours;
    }

    /**
     * @return string
     */
    public function getChannel(): string
    {
        return $this->channel;
    }

    /**
     * @return string
     */
    public function getOrigin(): string
    {
        return $this->origin;
    }

    /**
     * @return LineItem[]|array
     */
    public function getLineItems(): array
    {
        return $this->lineItems;
    }

    /**
     * @return AdditionalData|null
     */
    public function getAdditionalData(): ?AdditionalData
    {
        return $this->additionalData;
    }

    /**
     * @return AuthenticationData|null
     */
    public function getAuthenticationData(): ?AuthenticationData
    {
        return $this->authenticationData;
    }

    /**
     * @return string
     */
    public function getDeviceFingerprint(): string
    {
        return $this->deviceFingerprint;
    }

    /**
     * @return array
     */
    public function getBankAccount(): array
    {
        return $this->bankAccount;
    }
}
