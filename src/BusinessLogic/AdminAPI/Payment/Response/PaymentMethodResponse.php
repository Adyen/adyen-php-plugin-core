<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\Payment\Response;

use Adyen\Core\BusinessLogic\AdminAPI\Response\Response;
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

/**
 * Class PaymentMethodResponse
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\Payment\Response
 */
class PaymentMethodResponse extends Response
{
    /**
     * @var PaymentMethod
     */
    private $paymentMethod;

    /**
     * @param PaymentMethod|null $paymentMethod
     */
    public function __construct(?PaymentMethod $paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;
    }

    public function toArray(): array
    {
        if (!$this->paymentMethod) {
            return [];
        }

        return [
            'methodId' => $this->paymentMethod->getMethodId(),
            'logo' => $this->paymentMethod->getLogo(),
            'name' => $this->paymentMethod->getName(),
            'status' => $this->paymentMethod->isStatus(),
            'currencies' => $this->paymentMethod->getCurrencies(),
            'countries' => $this->paymentMethod->getCountries(),
            'paymentType' => $this->paymentMethod->getPaymentType(),
            'code' => $this->paymentMethod->getCode(),
            'description' => $this->paymentMethod->getDescription(),
            'surchargeType' => $this->paymentMethod->getSurchargeType(),
            'fixedSurcharge' => !empty(
            $this->paymentMethod->getFixedSurcharge()
            ) ? (float)$this->paymentMethod->getFixedSurcharge() : 0,
            'percentSurcharge' => $this->paymentMethod->getPercentSurcharge(),
            'surchargeLimit' => $this->paymentMethod->getSurchargeLimit(),
            'documentationUrl' => $this->paymentMethod->getDocumentationUrl(),
            'additionalData' => $this->paymentMethod->getAdditionalData() ?
                $this->transformAdditionalDataToArray(
                    $this->paymentMethod->getAdditionalData()
                ) : []
        ];
    }

    /**
     * @param PaymentMethodAdditionalData $data
     *
     * @return array
     */
    protected function transformAdditionalDataToArray(PaymentMethodAdditionalData $data): array
    {
        if ($data instanceof CardConfig) {
            return [
                'type' => CardConfig::class,
                'showLogos' => $data->isShowLogos(),
                'singleClickPayment' => $data->isSingleClickPayment(),
                'installments' => $data->isInstallments(),
                'installmentAmounts' => $data->isInstallmentAmounts(),
                'sendBasket' => $data->isSendBasket(),
                'installmentCountries' => $data->getInstallmentCountries() === CardConfig::INSTALLMENT_COUNTRIES ?
                    CardConfig::ANY : $data->getInstallmentCountries(),
                'minimumAmount' => $data->getMinimumAmount(),
                'numberOfInstallments' => empty($data->getNumberOfInstallments()) ? '' : implode(
                    ",",
                    $data->getNumberOfInstallments()
                ),
            ];
        }

        if ($data instanceof Oney) {
            return [
                'type' => Oney::class,
                'supportedInstallments' => $data->getSupportedInstallments(),
            ];
        }

        if ($data instanceof EPS) {
            return [
                'type' => EPS::class,
                'bankIssuer' => $data->getBankIssuer(),
            ];
        }

        if ($data instanceof IDEALonlineBankingThailand) {
            return [
                'type' => IDEALonlineBankingThailand::class,
                'showLogos' => $data->isShowLogos(),
                'bankIssuer' => $data->getBankIssuer(),
            ];
        }

        if ($data instanceof ApplePay) {
            return [
                'type' => ApplePay::class,
                'merchantName' => $data->getMerchantName(),
                'merchantId' => $data->getMerchantId(),
                'displayButtonOn' => $data->getDisplayButtonOn(),
            ];
        }

        if ($data instanceof AmazonPay) {
            return [
                'type' => AmazonPay::class,
                'publicKeyId' => $data->getPublicKeyId(),
                'merchantId' => $data->getMerchantId(),
                'storeId' => $data->getStoreId(),
                'displayButtonOn' => $data->getDisplayButtonOn(),
            ];
        }

        if ($data instanceof GooglePay) {
            return [
                'type' => GooglePay::class,
                'gatewayMerchantId' => $data->getGatewayMerchantId(),
                'merchantId' => $data->getMerchantId(),
                'displayButtonOn' => $data->getDisplayButtonOn(),
            ];
        }

        if ($data instanceof PayPal) {
            return [
                'type' => PayPal::class,
                'displayButtonOn' => $data->getDisplayButtonOn(),
            ];
        }

        return [];
    }
}
