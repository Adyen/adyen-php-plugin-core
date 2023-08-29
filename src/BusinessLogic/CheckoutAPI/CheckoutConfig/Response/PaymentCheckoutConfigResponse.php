<?php

namespace Adyen\Core\BusinessLogic\CheckoutAPI\CheckoutConfig\Response;

use Adyen\Core\BusinessLogic\AdminAPI\Response\Response;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PaymentCheckoutConfigResult;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PaymentMethodCode;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PaymentMethodResponse;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\MethodAdditionalData\AmazonPay;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\MethodAdditionalData\CardConfig;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\MethodAdditionalData\EPS;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\MethodAdditionalData\GooglePay;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\MethodAdditionalData\IDEALonlineBankingThailand;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\PaymentMethod;

/**
 * Class PaymentCheckoutConfigRequest
 *
 * @package Adyen\Core\BusinessLogic\CheckoutAPI\CheckoutConfig\Request
 */
class PaymentCheckoutConfigResponse extends Response
{
    /**
     * @var PaymentCheckoutConfigResult
     */
    private $result;
    /**
     * @var Amount
     */
    private $amount;
    /**
     * @var string
     */
    private $shopperLocale;
    /**
     * @var string
     */
    private $country;

    public function __construct(
        PaymentCheckoutConfigResult $result,
        Amount $amount,
        string $shopperLocale = 'en-US',
        string $country = ''
    ) {
        $this->result = $result;
        $this->amount = $amount;
        $this->shopperLocale = $shopperLocale;
        $this->country = $country;
    }

    /**
     * Gets the array of payment methods responses from Adyen API
     *
     * @return PaymentMethodResponse[]
     */
    public function getPaymentMethodResponse(): array
    {
        return $this->result->getAvailablePaymentMethodsResponse()->getPaymentMethodsResponse();
    }

    public function getStoredPaymentMethodResponse(): array
    {
        return $this->result->getAvailablePaymentMethodsResponse()->getStoredPaymentMethodsResponse();
    }

    /**
     * @return PaymentMethod[]
     */
    public function getPaymentMethodsConfiguration(): array
    {
        return $this->result->getPaymentMethodsConfiguration();
    }

    public function toArray(): array
    {
        $configurations = [];

        foreach ($this->result->getPaymentMethodsConfiguration() as $method) {
            if (PaymentMethodCode::scheme()->equals($method->getCode())) {
                /** @var CardConfig $additionalData */
                $additionalData = $method->getAdditionalData();
                if (!$additionalData) {
                    continue;
                }

                if ($this->country && in_array($this->country, $additionalData->getInstallmentCountries()) &&
                    $this->amount->getPriceInCurrencyUnits() >= $additionalData->getMinimumAmount()) {
                    $configurations['card']['showInstallmentAmounts'] = $additionalData->isInstallmentAmounts();
                    $configurations['card']['installmentOptions'] = [
                        'showInstallmentAmounts' => $additionalData->isInstallmentAmounts(),
                        'card' => [
                            'values' => $additionalData->getNumberOfInstallments(),
                            'plans' => ['regular']
                        ]
                    ];
                }

                $configurations['card']['showBrandsUnderCardNumber'] = $additionalData->isShowLogos();
                $configurations['card']['enableStoreDetails'] = $additionalData->isSingleClickPayment();
            }

            if (PaymentMethodCode::eps()->equals($method->getCode())) {
                /** @var EPS $additionalData */
                $additionalData = $method->getAdditionalData();

                if (!$additionalData) {
                    continue;
                }

                $configurations['eps']['placeholder'] = $additionalData->getBankIssuer();
            }

            if (
                PaymentMethodCode::googlePay()->equals($method->getCode()) ||
                PaymentMethodCode::payWithGoogle()->equals($method->getCode())
            ) {
                /** @var GooglePay $additionalData */
                $additionalData = $method->getAdditionalData();

                if (!$additionalData) {
                    continue;
                }

                if (!empty($additionalData->getGatewayMerchantId())) {
                    $configurations['googlepay']['gatewayMerchantId'] = $additionalData->getGatewayMerchantId();
                }

                if (!empty($additionalData->getMerchantId())) {
                    $configurations['googlepay']['merchantId'] = $additionalData->getMerchantId();
                }
            }

            if (PaymentMethodCode::amazonPay()->equals($method->getCode())) {
                /** @var AmazonPay $additionalData */
                $additionalData = $method->getAdditionalData();

                if (!$additionalData) {
                    continue;
                }

                $configurations[$method->getCode()] = [
                    'configuration' => [
                        'merchantId' => $additionalData->getMerchantId(),
                        'publicKeyId' => $additionalData->getPublicKeyId(),
                        'storeId' => $additionalData->getStoreId(),
                    ],
                ];
            }

            if (PaymentMethodCode::ideal()->equals($method->getCode()) ||
                PaymentMethodCode::molPayEBankingTh()->equals($method->getCode())) {
                /** @var IDEALonlineBankingThailand $additionalData */
                $additionalData = $method->getAdditionalData();

                if (!$additionalData) {
                    continue;
                }

                $configurations[$method->getCode()] = [
                    'showImage' => $additionalData->isShowLogos(),
                ];

                if ($additionalData->getBankIssuer()) {
                    $configurations[$method->getCode()]['placeholder'] = $additionalData->getBankIssuer();
                }
            }
        }

        return [
            'locale' => $this->shopperLocale,
            'environment' => $this->result->getConnectionMode(),
            'clientKey' => $this->result->getClientKey(),
            'showPayButton' => false,
            'countryCode' => $this->country,
            'amount' => [
                'value' => $this->amount->getValue(),
                'currency' => $this->amount->getCurrency()->getIsoCode(),
            ],
            'paymentMethodsResponse' => [
                'paymentMethods' => array_map(static function (PaymentMethodResponse $methodResponse) {
                    return $methodResponse->getMetaData();
                }, $this->result->getAvailablePaymentMethodsResponse()->getPaymentMethodsResponse()),
                'storedPaymentMethods' => array_map(static function (PaymentMethodResponse $methodResponse) {
                    return $methodResponse->getMetaData();
                }, $this->result->getAvailablePaymentMethodsResponse()->getStoredPaymentMethodsResponse()),
            ],
            'paymentMethodsConfiguration' => $configurations
        ];
    }
}
