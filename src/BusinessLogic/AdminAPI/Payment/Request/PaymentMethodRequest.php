<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\Payment\Request;

use Adyen\Core\BusinessLogic\AdminAPI\Request\Request;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\InvalidPaymentMethodCodeException;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PaymentMethodCode;
use Adyen\Core\BusinessLogic\Domain\Payment\Exceptions\DuplicatedValuesNotAllowedException;
use Adyen\Core\BusinessLogic\Domain\Payment\Exceptions\InvalidCardConfigurationException;
use Adyen\Core\BusinessLogic\Domain\Payment\Exceptions\NegativeValuesNotAllowedException;
use Adyen\Core\BusinessLogic\Domain\Payment\Exceptions\PaymentMethodDataEmptyException;
use Adyen\Core\BusinessLogic\Domain\Payment\Exceptions\StringValuesNotAllowedException;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\AuthorizationType;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\Exceptions\InvalidAuthorizationTypeException;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\Exceptions\InvalidTokenTypeException;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\MethodAdditionalData\AmazonPay;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\MethodAdditionalData\ApplePay;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\MethodAdditionalData\CardConfig;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\MethodAdditionalData\EPS;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\MethodAdditionalData\GooglePay;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\MethodAdditionalData\IDEALonlineBankingThailand;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\MethodAdditionalData\Oney;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\MethodAdditionalData\PaymentMethodAdditionalData;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\MethodAdditionalData\PayPal;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\PaymentMethod;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\TokenType;

/**
 * Class PaymentMethodRequest
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\Payment\Request
 */
class PaymentMethodRequest extends Request
{
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
    private $publicKeyId;
    /**
     * @var string
     */
    private $merchantId;
    /**
     * @var string
     */
    private $storeId;
    /**
     * @var string
     */
    private $displayButtonOn;
    /**
     * @var string
     */
    private $merchantName;
    /**
     * @var bool
     */
    private $showLogos;
    /**
     * @var bool
     */
    private $singleClickPayment;
    /**
     * @var bool
     */
    private $clickToPay;
    /**
     * @var bool
     */
    private $installments;
    /**
     * @var bool
     */
    private $installmentAmounts;
    /**
     * @var bool
     */
    private $sendBasket;
    /**
     * @var string[]
     */
    private $installmentCountries;
    /**
     * @var float
     */
    private $minimumAmount;
    /**
     * @var string[]
     */
    private $numberOfInstallments;
    /**
     * @var string
     */
    private $bankIssuer;
    /**
     * @var string
     */
    private $gatewayMerchantId;
    /**
     * @var string[]
     */
    private $supportedInstallments;
    /**
     * @var bool
     */
    private $excludeFromPayByLink;
    /**
     * @var bool
     */
    private $enableTokenization;
    /**
     * @var string
     */
    private $tokenType;
    /**
     * @var string
     */
    private $authorizationType;

    /**
     * @param string $methodId
     * @param string $logo
     * @param string $name
     * @param bool $status
     * @param string[] $currencies
     * @param string[] $countries
     * @param string $paymentType
     * @param string $code
     * @param string $description
     * @param string $surchargeType
     * @param string $fixedSurcharge
     * @param float|null $percentSurcharge
     * @param float|null $surchargeLimit
     * @param string $publicKeyId
     * @param string $merchantId
     * @param string $storeId
     * @param string $displayButtonOn
     * @param string $merchantName
     * @param bool $showLogos
     * @param bool $singleClickPayment
     * @param bool $clickToPay
     * @param bool $installments
     * @param bool $installmentAmounts
     * @param bool $sendBasket
     * @param array $installmentCountries
     * @param float $minimumAmount
     * @param array $numberOfInstallments
     * @param string $bankIssuer
     * @param string $gatewayMerchantId
     * @param string[] $supportedInstallments
     * @param bool $excludeFromPayByLink
     * @param bool $enableTokenization
     * @param string $tokenType
     * @param string $authorizationType
     */
    private function __construct(
        string $methodId = '',
        string $logo = '',
        string $name = '',
        bool $status = false,
        array $currencies = [],
        array $countries = [],
        string $paymentType = '',
        string $code = '',
        string $description = '',
        string $surchargeType = '',
        string $fixedSurcharge = '',
        float $percentSurcharge = null,
        float $surchargeLimit = null,
        string $publicKeyId = '',
        string $merchantId = '',
        string $storeId = '',
        string $displayButtonOn = '',
        string $merchantName = '',
        bool $showLogos = false,
        bool $singleClickPayment = false,
        bool $clickToPay = false,
        bool $installments = false,
        bool $installmentAmounts = false,
        bool $sendBasket = false,
        array $installmentCountries = [],
        float $minimumAmount = 0,
        array $numberOfInstallments = [],
        string $bankIssuer = '',
        string $gatewayMerchantId = '',
        array $supportedInstallments = [],
        bool $excludeFromPayByLink = false,
        bool $enableTokenization = false,
        string $tokenType = '',
        string $authorizationType = ''
    ) {
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
        $this->publicKeyId = $publicKeyId;
        $this->merchantId = $merchantId;
        $this->storeId = $storeId;
        $this->displayButtonOn = $displayButtonOn;
        $this->merchantName = $merchantName;
        $this->showLogos = $showLogos;
        $this->singleClickPayment = $singleClickPayment;
        $this->clickToPay = $clickToPay;
        $this->installments = $installments;
        $this->installmentAmounts = $installmentAmounts;
        $this->sendBasket = $sendBasket;
        $this->installmentCountries = $installmentCountries;
        $this->minimumAmount = $minimumAmount;
        $this->numberOfInstallments = $numberOfInstallments;
        $this->bankIssuer = $bankIssuer;
        $this->gatewayMerchantId = $gatewayMerchantId;
        $this->supportedInstallments = $supportedInstallments;
        $this->excludeFromPayByLink = $excludeFromPayByLink;
        $this->enableTokenization = $enableTokenization;
        $this->tokenType = $tokenType;
        $this->authorizationType = $authorizationType;
    }

    /**
     * @param array $rawData
     *
     * @return PaymentMethodRequest
     */
    public static function parse(array $rawData): PaymentMethodRequest
    {
        return new PaymentMethodRequest(
            $rawData['methodId'] ?? '',
            $rawData['logo'],
            $rawData['name'] ?? '',
            true,
            $rawData['currencies'] ?? [],
            $rawData['countries'] ?? [],
            $rawData['type'] ?? '',
            $rawData['code'] ?? '',
            $rawData['description'] ?? '',
            $rawData['surchargeType'] ?? '',
            $rawData['fixedSurcharge'] ?? '',
            !empty($rawData['percentSurcharge']) ? $rawData['percentSurcharge'] : null,
            !empty($rawData['surchargeLimit']) && $rawData['surchargeLimit'] !== 'null' ? $rawData['surchargeLimit'] : null,
            $rawData['additionalData']['publicKeyId'] ?? '',
            $rawData['additionalData']['merchantId'] ?? '',
            $rawData['additionalData']['storeId'] ?? '',
            $rawData['additionalData']['displayButtonOn'] ?? '',
            $rawData['additionalData']['merchantName'] ?? '',
            $rawData['additionalData']['showLogos'] ?? false,
            $rawData['additionalData']['singleClickPayment'] ?? false,
            $rawData['additionalData']['clickToPay'] ?? false,
            $rawData['additionalData']['installments'] ?? false,
            $rawData['additionalData']['installmentAmounts'] ?? false,
            $rawData['additionalData']['sendBasket'] ?? false,
            $rawData['additionalData']['installmentCountries'] ?? [],
            !empty($rawData['additionalData']['minimumAmount']) ? $rawData['additionalData']['minimumAmount'] : 0,
            !empty($rawData['additionalData']['numberOfInstallments']) && !is_array(
                $rawData['additionalData']['numberOfInstallments']
            ) ?
                array_map(static function (string $installment) {
                    return trim($installment);
                },
                    explode(',', $rawData['additionalData']['numberOfInstallments'])
                ) : (!empty($rawData['additionalData']['numberOfInstallments']) ? $rawData['additionalData']['numberOfInstallments'] : []),
            $rawData['additionalData']['bankIssuer'] ?? '',
            $rawData['additionalData']['gatewayMerchantId'] ?? '',
            !empty($rawData['additionalData']['supportedInstallments']) ?
                $rawData['additionalData']['supportedInstallments'] : [],
            !empty($rawData['excludeFromPayByLink']) && $rawData['excludeFromPayByLink'] === 'true',
            !empty($rawData['enableTokenization']) && $rawData['enableTokenization'] === 'true',
            $rawData['tokenType'] ?? '',
            $rawData['authorizationType'] ?? ''
        );
    }

    /**
     * @inheritDoc
     *
     * @throws DuplicatedValuesNotAllowedException
     * @throws InvalidCardConfigurationException
     * @throws NegativeValuesNotAllowedException
     * @throws PaymentMethodDataEmptyException
     * @throws StringValuesNotAllowedException|
     * @throws InvalidPaymentMethodCodeException
     * @throws InvalidTokenTypeException
     * @throws InvalidAuthorizationTypeException
     */
    public function transformToDomainModel(): object
    {
        return new PaymentMethod(
            $this->methodId,
            $this->code,
            $this->name,
            $this->logo,
            $this->status,
            $this->currencies,
            $this->countries,
            $this->paymentType,
            $this->description,
            $this->surchargeType,
            $this->fixedSurcharge,
            $this->percentSurcharge,
            $this->surchargeLimit,
            '',
            $this->transformAdditionalData(),
            $this->excludeFromPayByLink,
            $this->enableTokenization,
            !empty($this->tokenType) ? TokenType::fromState($this->tokenType) : null,
            !empty($this->authorizationType) ? AuthorizationType::fromState($this->authorizationType) : null
        );
    }

    /**
     * @return PaymentMethodAdditionalData|null
     *
     * @throws DuplicatedValuesNotAllowedException
     * @throws InvalidCardConfigurationException
     * @throws NegativeValuesNotAllowedException
     * @throws StringValuesNotAllowedException
     */
    private function transformAdditionalData(): ?PaymentMethodAdditionalData
    {
        if (PaymentMethodCode::amazonPay()->equals($this->code)) {
            return new AmazonPay($this->publicKeyId, $this->merchantId, $this->storeId, $this->displayButtonOn);
        }

        if (PaymentMethodCode::applePay()->equals($this->code)) {
            return new ApplePay($this->merchantName, $this->merchantId, $this->displayButtonOn);
        }

        if (PaymentMethodCode::scheme()->equals($this->code)) {
            return new CardConfig(
                $this->showLogos,
                $this->singleClickPayment,
                $this->clickToPay,
                $this->installments,
                $this->installmentAmounts,
                $this->sendBasket,
                $this->installmentCountries,
                $this->minimumAmount,
                $this->numberOfInstallments
            );
        }

        if (PaymentMethodCode::eps()->equals($this->code)) {
            return new EPS($this->bankIssuer);
        }

        if (PaymentMethodCode::googlePay()->equals($this->code) ||
            PaymentMethodCode::payWithGoogle()->equals($this->code)) {
            return new GooglePay($this->gatewayMerchantId, $this->merchantId, $this->displayButtonOn);
        }

        if (PaymentMethodCode::ideal()->equals($this->code) ||
            PaymentMethodCode::molPayEBankingTh()->equals($this->code)) {
            return new IDEALonlineBankingThailand($this->showLogos, $this->bankIssuer);
        }

        if (PaymentMethodCode::isOneyMethod($this->code)) {
            return new Oney($this->supportedInstallments);
        }

        if (PaymentMethodCode::payPal()->equals($this->code)) {
            return new PayPal($this->displayButtonOn);
        }

        return null;
    }
}
