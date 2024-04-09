<?php

namespace Adyen\Core\BusinessLogic\Domain\Payment\Models;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\InvalidPaymentMethodCodeException;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PaymentMethodCode;
use Adyen\Core\BusinessLogic\Domain\Payment\Enum\PaymentMethodType;
use Adyen\Core\BusinessLogic\Domain\Payment\Exceptions\PaymentMethodDataEmptyException;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\MethodAdditionalData\PaymentMethodAdditionalData;
use Adyen\Core\BusinessLogic\Domain\Payment\Services\PaymentService;
use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class PaymentMethod
 *
 * @package Adyen\Core\BusinessLogic\Domain\Payment\Models
 */
class PaymentMethod
{
    public const CREDIT_CARD_LOGO = 'card';
    /**
     * @var string
     */
    private $methodId;
    /**
     * @var string
     */
    private $logo;
    /**
     * @var string
     */
    private $name;
    /**
     * @var bool
     */
    private $status;
    /**
     * @var string[]
     */
    private $currencies;
    /**
     * @var string[]
     */
    private $countries;
    /**
     * @var string
     */
    private $paymentType;
    /**
     * @var string
     */
    private $code;
    /**
     * @var string
     */
    private $description;
    /**
     * @var string
     */
    private $surchargeType;
    /**
     * @var string
     */
    private $fixedSurcharge;
    /**
     * @var float
     */
    private $percentSurcharge;
    /**
     * @var float
     */
    private $surchargeLimit;
    /**
     * @var string
     */
    private $documentationUrl;
    /**
     * @var PaymentMethodAdditionalData
     */
    private $additionalData;
    /**
     * @var bool
     */
    private $excludeFromPayByLink;

    /**
     * @var bool
     */
    private $supportsPaymentLink;

    /**
     * @var bool
     */
    private $enableTokenization;

    /**
     * @var TokenType|null
     */
    private $tokenType;

    /**
     * @var bool
     */
    private $supportsRecurringPayments;

    /**
     * @var AuthorizationType|null
     */
    private $authorizationType;

    /**
     * @param string $methodId
     * @param string $code
     * @param string $name
     * @param string $logo
     * @param bool $status
     * @param string[] $currencies
     * @param string[] $countries
     * @param string $paymentType
     * @param string $description
     * @param string $surchargeType
     * @param string $fixedSurcharge
     * @param float|null $percentSurcharge
     * @param float|null $surchargeLimit
     * @param string $documentationUrl
     * @param PaymentMethodAdditionalData|null $additionalData
     * @param bool $excludeFromPayByLink
     * @param bool $enableTokenization
     * @param TokenType|null $tokenType
     * @param AuthorizationType|null $authorizationType
     * @throws InvalidPaymentMethodCodeException
     * @throws PaymentMethodDataEmptyException
     */
    public function __construct(
        string $methodId,
        string $code,
        string $name,
        string $logo = '',
        bool $status = true,
        array $currencies = [],
        array $countries = [],
        string $paymentType = '',
        string $description = '',
        string $surchargeType = '',
        string $fixedSurcharge = '',
        float $percentSurcharge = null,
        ?float $surchargeLimit = null,
        string $documentationUrl = '',
        ?PaymentMethodAdditionalData $additionalData = null,
        bool $excludeFromPayByLink = false,
        bool $enableTokenization = false,
        ?TokenType $tokenType = null,
        ?AuthorizationType $authorizationType = null
    ) {
        if (
            empty($methodId) ||
            empty($name) ||
            empty($code)
        ) {
            throw new PaymentMethodDataEmptyException(
                new TranslatableLabel('Empty payment method data.', 'payments.emptyDataError')
            );
        }

        if (empty($logo)) {
            $logo = self::getLogoUrl($code);
        }

        if (empty($paymentType)) {
            $paymentType = self::getType($code);
        }

        $this->methodId = $methodId;
        $this->logo = $logo;
        $this->name = $name;
        $this->status = $status;
        $this->currencies = $currencies;
        $this->countries = $countries;
        $this->paymentType = $paymentType;
        $this->code = $code;
        $this->description = $description;
        $this->surchargeType = $surchargeType;
        $this->fixedSurcharge = $fixedSurcharge;
        $this->percentSurcharge = $percentSurcharge;
        $this->surchargeLimit = $surchargeLimit;
        $this->documentationUrl = $documentationUrl;
        $this->additionalData = $additionalData;
        $this->excludeFromPayByLink = $excludeFromPayByLink;
        $this->supportsPaymentLink = PaymentMethodCode::parse($code)->isPaymentLinkSupported();
        $this->enableTokenization = $enableTokenization;
        $this->tokenType = $tokenType;
        $this->supportsRecurringPayments = PaymentMethodCode::parse($code)->isRecurringPaymentSupported();
        $this->authorizationType = $authorizationType;
    }

    /**
     * @param string $code
     *
     * @return string
     */
    public static function getType(string $code): string
    {
        $codeToCheck = PaymentMethodCode::isGiftCard($code) ? (string)PaymentMethodCode::giftCard() : $code;

        return static::getPaymentMethodTypes()[$codeToCheck] ?? '';
    }

    /**
     * @param string $code
     *
     * @return string
     */
    public static function getLogoUrl(string $code): string
    {
        if ($code === PaymentService::CREDIT_CARD_CODE) {
            $code = self::CREDIT_CARD_LOGO;
        }

        return 'https://checkoutshopper-live.adyen.com/checkoutshopper/images/logos/' . $code . '.svg';
    }

    /**
     * @return string
     */
    public function getMethodId(): string
    {
        return $this->methodId;
    }

    /**
     * @param string $methodId
     */
    public function setMethodId(string $methodId): void
    {
        $this->methodId = $methodId;
    }

    /**
     * @return string
     */
    public function getLogo(): string
    {
        return $this->logo;
    }

    /**
     * @param string $logo
     */
    public function setLogo(string $logo): void
    {
        $this->logo = $logo;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return bool
     */
    public function isStatus(): bool
    {
        return $this->status;
    }

    /**
     * @param bool $status
     */
    public function setStatus(bool $status): void
    {
        $this->status = $status;
    }

    /**
     * @return string[]
     */
    public function getCurrencies(): array
    {
        return $this->currencies;
    }

    /**
     * @param string[] $currencies
     */
    public function setCurrencies(array $currencies): void
    {
        $this->currencies = $currencies;
    }

    /**
     * @return string[]
     */
    public function getCountries(): array
    {
        return $this->countries;
    }

    /**
     * @param string[] $countries
     */
    public function setCountries(array $countries): void
    {
        $this->countries = $countries;
    }

    /**
     * @return string
     */
    public function getPaymentType(): string
    {
        return $this->paymentType;
    }

    /**
     * @param string $paymentType
     */
    public function setPaymentType(string $paymentType): void
    {
        $this->paymentType = $paymentType;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getSurchargeType(): string
    {
        return $this->surchargeType;
    }

    /**
     * @param string $surchargeType
     */
    public function setSurchargeType(string $surchargeType): void
    {
        $this->surchargeType = $surchargeType;
    }

    /**
     * @return string
     */
    public function getFixedSurcharge(): string
    {
        return $this->fixedSurcharge;
    }

    /**
     * @param string $fixedSurcharge
     */
    public function setFixedSurcharge(string $fixedSurcharge): void
    {
        $this->fixedSurcharge = $fixedSurcharge;
    }

    /**
     * @return float|int
     */
    public function getPercentSurcharge()
    {
        return $this->percentSurcharge;
    }

    /**
     * @param float|int $percentSurcharge
     */
    public function setPercentSurcharge($percentSurcharge): void
    {
        $this->percentSurcharge = $percentSurcharge;
    }

    /**
     * @return float|int
     */
    public function getSurchargeLimit()
    {
        return $this->surchargeLimit;
    }

    /**
     * @param float|int $surchargeLimit
     */
    public function setSurchargeLimit($surchargeLimit): void
    {
        $this->surchargeLimit = $surchargeLimit;
    }

    /**
     * @return bool
     */
    public function getExcludeFromPayByLink(): bool
    {
        return $this->excludeFromPayByLink;
    }

    /**
     * @param bool $excludeFromPayByLink
     *
     * @return void
     */
    public function setExcludeFromPayByLink(bool $excludeFromPayByLink): void
    {
        $this->excludeFromPayByLink = $excludeFromPayByLink;
    }

    /**
     * Gets the total surcharge amount considering fixed, percentage surcharge, and surcharge limit.
     *
     * @param float $cartAmount The current checkout cart amount value to base percentage calculation on.
     * @return float The total surcharge amount value
     */
    public function getTotalSurchargeFor(float $cartAmount): float
    {
        if ($this->getSurchargeType() === 'none') {
            return 0;
        }

        $fixedSurcharge = !empty($this->getFixedSurcharge()) ? $this->getFixedSurcharge() : 0;
        $percentSurcharge = !empty($this->getPercentSurcharge()) ? $this->getPercentSurcharge() : 0;
        $percentSurchargeAmount = $cartAmount / 100 * $percentSurcharge;
        if (!empty($this->getSurchargeLimit())) {
            $percentSurchargeAmount = min($percentSurchargeAmount, $this->getSurchargeLimit());
        }

        return $fixedSurcharge + $percentSurchargeAmount;
    }

    /**
     * @return string
     */
    public function getDocumentationUrl(): string
    {
        return $this->documentationUrl;
    }

    /**
     * @param string $documentationUrl
     */
    public function setDocumentationUrl(string $documentationUrl): void
    {
        $this->documentationUrl = $documentationUrl;
    }

    /**
     * @return PaymentMethodAdditionalData|null
     */
    public function getAdditionalData(): ?PaymentMethodAdditionalData
    {
        return $this->additionalData;
    }

    /**
     * @param PaymentMethodAdditionalData|null $additionalData
     */
    public function setAdditionalData(?PaymentMethodAdditionalData $additionalData): void
    {
        $this->additionalData = $additionalData;
    }

    /**
     * @return bool
     */
    public function getSupportsPaymentLink(): bool
    {
        return $this->supportsPaymentLink;
    }

    /**
     * @param bool $supportsPaymentLink
     *
     * @return void
     */
    public function setSupportsPaymentLink(bool $supportsPaymentLink): void
    {
        $this->supportsPaymentLink = $supportsPaymentLink;
    }

    /**
     * @return bool
     */
    public function getEnableTokenization(): bool
    {
        return $this->enableTokenization;
    }

    /**
     * @param bool $enableTokenization
     *
     * @return void
     */
    public function setEnableTokenization(bool $enableTokenization): void
    {
        $this->enableTokenization = $enableTokenization;
    }

    /**
     * @return TokenType|null
     */
    public function getTokenType(): ?TokenType
    {
        return $this->tokenType;
    }

    /**
     * @param TokenType|null $tokenType
     *
     * @return void
     */
    public function setTokenType(?TokenType $tokenType): void
    {
        $this->tokenType = $tokenType;
    }

    /**
     * @return bool
     */
    public function getSupportsRecurringPayments(): bool
    {
        return $this->supportsRecurringPayments;
    }

    /**
     * @param bool $supportsTokenization
     *
     * @return void
     */
    public function setSupportsRecurringPayments(bool $supportsTokenization): void
    {
        $this->supportsRecurringPayments = $supportsTokenization;
    }

    /**
     * @return AuthorizationType|null
     */
    public function getAuthorizationType(): ?AuthorizationType
    {
        return $this->authorizationType;
    }

    /**
     * @param AuthorizationType|null $authorizationType
     *
     * @return void
     */
    public function setAuthorizationType(?AuthorizationType $authorizationType): void
    {
        $this->authorizationType = $authorizationType;
    }

    /**
     * @return string[]
     */
    protected static function getPaymentMethodTypes(): array
    {
        return PaymentMethodType::PAYMENT_METHOD_TYPES;
    }
}
