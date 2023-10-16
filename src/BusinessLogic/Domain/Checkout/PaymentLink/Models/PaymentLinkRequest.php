<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Models;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\ApplicationInfo\ApplicationInfo;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\BillingAddress;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\DeliveryAddress;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\LineItem;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\ShopperName;

/**
 * Class PaymentLinkRequest.
 *
 * @package Adyen\Core\BusinessLogic\Domain\PaymentLink\Models
 */
class PaymentLinkRequest
{
    /**
     * @var string
     */
    private $reference;

    /**
     * @var string
     */
    private $merchantAccount;

    /**
     * @var Amount
     */
    private $amount;

    /**
     * @var array
     */
    private $allowedPaymentMethods;

    /**
     * @var string
     */
    private $countryCode;

    /**
     * @var string
     */
    private $shopperReference;

    /**
     * @var string
     */
    private $shopperEmail;

    /**
     * @var string
     */
    private $shopperLocale;

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
    private $captureDelayHours;

    /**
     * @var bool
     */
    private $manualCapture;

    /**
     * @var string
     */
    private $shopperName;

    /**
     * @var string
     */
    private $dateOfBirth;

    /**
     * @var ApplicationInfo
     */
    private $applicationInfo;

    /**
     * @var LineItem[]
     */
    private $lineItems;

    /**
     * @param string $reference
     * @param string $merchantAccount
     * @param Amount $amount
     * @param array $allowedPaymentMethods
     * @param string|null $countryCode
     * @param string|null $shopperReference
     * @param string|null $shopperEmail
     * @param string|null $shopperLocale
     * @param BillingAddress|null $billingAddress
     * @param DeliveryAddress|null $deliveryAddress
     * @param int $captureDelayHours
     */
    public function __construct(
        string $reference,
        string $merchantAccount,
        Amount $amount,
        array $allowedPaymentMethods = [],
        ?string $countryCode = null,
        ?string $shopperReference = null,
        ?string $shopperEmail = null,
        ?string $shopperLocale = null,
        ?BillingAddress $billingAddress = null,
        ?DeliveryAddress $deliveryAddress = null,
        int $captureDelayHours = -1,
        bool $manualCapture = false,
        ?ShopperName $shopperName = null,
        ?string $dateOfBirth = null,
        ?ApplicationInfo $applicationInfo = null,
        array $lineItems = []
    ) {
        $this->reference = $reference;
        $this->merchantAccount = $merchantAccount;
        $this->amount = $amount;
        $this->allowedPaymentMethods = $allowedPaymentMethods;
        $this->countryCode = $countryCode;
        $this->shopperReference = $shopperReference;
        $this->shopperEmail = $shopperEmail;
        $this->shopperLocale = $shopperLocale;
        $this->billingAddress = $billingAddress;
        $this->deliveryAddress = $deliveryAddress;
        $this->captureDelayHours = $captureDelayHours;
        $this->manualCapture = $manualCapture;
        $this->shopperName = $shopperName;
        $this->dateOfBirth = $dateOfBirth;
        $this->applicationInfo = $applicationInfo;
        $this->lineItems = $lineItems;
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
    public function getMerchantAccount(): string
    {
        return $this->merchantAccount;
    }

    /**
     * @return Amount
     */
    public function getAmount(): Amount
    {
        return $this->amount;
    }

    /**
     * @return array
     */
    public function getAllowedPaymentMethods(): array
    {
        return $this->allowedPaymentMethods;
    }

    /**
     * @return string|null
     */
    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    /**
     * @return string|null
     */
    public function getShopperReference(): ?string
    {
        return $this->shopperReference;
    }

    /**
     * @return string|null
     */
    public function getShopperEmail(): ?string
    {
        return $this->shopperEmail;
    }

    /**
     * @return string|null
     */
    public function getShopperLocale(): ?string
    {
        return $this->shopperLocale;
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
     * @return int
     */
    public function getCaptureDelayHours(): int
    {
        return $this->captureDelayHours;
    }

    /**
     * @return bool
     */
    public function getManualCapture(): bool
    {
        return $this->manualCapture;
    }

    /**
     * @return ShopperName|null
     */
    public function getShopperName(): ?ShopperName
    {
        return $this->shopperName;
    }

    /**
     * @return string|null
     */
    public function getDateOfBirth(): ?string
    {
        return $this->dateOfBirth;
    }

    /**
     * @return ApplicationInfo|null
     */
    public function getApplicationInfo(): ?ApplicationInfo
    {
        return $this->applicationInfo;
    }

    /**
     * @return LineItem[]
     */
    public function getLineItems(): array
    {
        return $this->lineItems;
    }
}
