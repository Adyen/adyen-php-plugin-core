<?php

namespace Adyen\Core\BusinessLogic\DataAccess\Payment\Entities;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PaymentMethodCode;
use Adyen\Core\BusinessLogic\Domain\Payment\Exceptions\PaymentMethodDataEmptyException;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\MethodAdditionalData\AmazonPay;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\MethodAdditionalData\ApplePay;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\MethodAdditionalData\CardConfig;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\MethodAdditionalData\EPS;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\MethodAdditionalData\GooglePay;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\MethodAdditionalData\IDEALonlineBankingThailand;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\MethodAdditionalData\Oney;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\MethodAdditionalData\PaymentMethodAdditionalData;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\MethodAdditionalData\PayPal;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\PaymentMethod as PaymentMethodModel;
use Adyen\Core\Infrastructure\Exceptions\BaseException;
use Adyen\Core\Infrastructure\ORM\Configuration\EntityConfiguration;
use Adyen\Core\Infrastructure\ORM\Configuration\IndexMap;
use Adyen\Core\Infrastructure\ORM\Entity;

/**
 * Class PaymentMethod
 *
 * @package Adyen\Core\BusinessLogic\DataAccess\Payment\Entities
 */
class PaymentMethod extends Entity
{
    /**
     * Fully qualified name of this class.
     */
    public const CLASS_NAME = __CLASS__;
    /**
     * @var string
     */
    protected $storeId;
    /**
     * @var string
     */
    protected $methodId;
    /**
     * @var string
     */
    protected $code;
    /**
     * @var PaymentMethodModel
     */
    protected $paymentMethod;
    protected $fields = ['id', 'storeId', 'methodId', 'code'];

    /**
     * @inheritDoc
     */
    public function getConfig(): EntityConfiguration
    {
        $indexMap = new IndexMap();
        $indexMap->addStringIndex('storeId')
            ->addStringIndex('methodId')
            ->addStringIndex('code');

        return new EntityConfiguration($indexMap, 'PaymentMethod');
    }

    /**
     * @inheritDoc
     *
     * @throws BaseException
     * @throws PaymentMethodDataEmptyException
     */
    public function inflate(array $data): void
    {
        parent::inflate($data);

        $paymentMethodData = static::getDataValue($data, 'paymentMethod', []);
        $this->paymentMethod = new PaymentMethodModel(
            static::getDataValue($paymentMethodData, 'methodId'),
            static::getDataValue($paymentMethodData, 'code'),
            static::getDataValue($paymentMethodData, 'name'),
            static::getDataValue($paymentMethodData, 'logo'),
            static::getDataValue($paymentMethodData, 'status', false),
            static::getDataValue($paymentMethodData, 'currencies', []),
            static::getDataValue($paymentMethodData, 'countries', []),
            static::getDataValue($paymentMethodData, 'paymentType'),
            static::getDataValue($paymentMethodData, 'description'),
            static::getDataValue($paymentMethodData, 'surchargeType'),
            static::getDataValue($paymentMethodData, 'fixedSurcharge', 0),
            static::getDataValue($paymentMethodData, 'percentSurcharge', 0),
            static::getDataValue($paymentMethodData, 'surchargeLimit', null),
            static::getDataValue($paymentMethodData, 'documentationUrl'),
            $this->transformAdditionalData($paymentMethodData)
        );
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $data = parent::toArray();
        $data['paymentMethod'] = [
            'methodId' => $this->paymentMethod->getMethodId(),
            'logo' => $this->paymentMethod->getLogo(),
            'name' => $this->paymentMethod->getName(),
            'status' => $this->paymentMethod->isStatus(),
            'paymentType' => $this->paymentMethod->getPaymentType(),
            'code' => $this->paymentMethod->getCode(),
            'description' => $this->paymentMethod->getDescription(),
            'surchargeType' => $this->paymentMethod->getSurchargeType(),
            'fixedSurcharge' => $this->paymentMethod->getFixedSurcharge(),
            'percentSurcharge' => $this->paymentMethod->getPercentSurcharge(),
            'surchargeLimit' => $this->paymentMethod->getSurchargeLimit(),
            'documentationUrl' => $this->paymentMethod->getDocumentationUrl(),
            'countries' => $this->paymentMethod->getCountries(),
            'currencies' => $this->paymentMethod->getCurrencies(),
        ];

        if ($this->paymentMethod->getAdditionalData()) {
            $data['paymentMethod']['additionalData'] = $this->transformAdditionalDataToArray(
                $this->paymentMethod->getAdditionalData()
            );
        }

        return $data;
    }

    /**
     * @return string
     */
    public function getStoreId(): string
    {
        return $this->storeId;
    }

    /**
     * @param string $storeId
     */
    public function setStoreId(string $storeId): void
    {
        $this->storeId = $storeId;
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
     * @return PaymentMethodModel
     */
    public function getPaymentMethod(): PaymentMethodModel
    {
        return $this->paymentMethod;
    }

    /**
     * @param PaymentMethodModel $paymentMethod
     */
    public function setPaymentMethod(PaymentMethodModel $paymentMethod): void
    {
        $this->paymentMethod = $paymentMethod;
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
                'installmentCountries' => $data->getInstallmentCountries(),
                'minimumAmount' => $data->getMinimumAmount(),
                'numberOfInstallments' => $data->getNumberOfInstallments(),
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

    /**
     * @param array $data
     *
     * @return PaymentMethodAdditionalData|null
     *
     * @throws BaseException
     */
    protected function transformAdditionalData(array $data): ?PaymentMethodAdditionalData
    {
        $paymentCode = static::getDataValue($data, 'code');
        if (empty($paymentCode)) {
            return null;
        }

        $additionalData = static::getDataValue($data, 'additionalData', []);
        if (PaymentMethodCode::scheme()->equals($paymentCode)) {
            return new CardConfig(
                static::getDataValue($additionalData, 'showLogos', false),
                static::getDataValue($additionalData, 'singleClickPayment', false),
                static::getDataValue($additionalData, 'installments', false),
                static::getDataValue($additionalData, 'installmentAmounts', false),
                static::getDataValue($additionalData, 'sendBasket', false),
                static::getDataValue($additionalData, 'installmentCountries', []),
                static::getDataValue($additionalData, 'minimumAmount', 0),
                static::getDataValue($additionalData, 'numberOfInstallments', [])
            );
        }

        if (PaymentMethodCode::oney()->equals($paymentCode)) {
            return new Oney(static::getDataValue($additionalData, 'supportedInstallments', []));
        }

        if (PaymentMethodCode::eps()->equals($paymentCode)) {
            return new EPS(static::getDataValue($additionalData, 'bankIssuer'));
        }

        if (
            PaymentMethodCode::ideal()->equals($paymentCode) ||
            PaymentMethodCode::molPayEBankingTh()->equals($paymentCode)
        ) {
            return new IDEALonlineBankingThailand(
                static::getDataValue($additionalData, 'showLogos', false),
                static::getDataValue($additionalData, 'bankIssuer')
            );
        }

        if (PaymentMethodCode::applePay()->equals($paymentCode)) {
            return new ApplePay(
                static::getDataValue($additionalData, 'merchantName'),
                static::getDataValue($additionalData, 'merchantId'),
                static::getDataValue($additionalData, 'displayButtonOn')
            );
        }

        if (PaymentMethodCode::amazonPay()->equals($paymentCode)) {
            return new AmazonPay(
                static::getDataValue($additionalData, 'publicKeyId'),
                static::getDataValue($additionalData, 'merchantId'),
                static::getDataValue($additionalData, 'storeId'),
                static::getDataValue($additionalData, 'displayButtonOn')
            );
        }

        if (
            PaymentMethodCode::googlePay()->equals($paymentCode) ||
            PaymentMethodCode::payWithGoogle()->equals($paymentCode)
        ) {
            return new GooglePay(
                static::getDataValue($additionalData, 'gatewayMerchantId'),
                static::getDataValue($additionalData, 'merchantId'),
                static::getDataValue($additionalData, 'displayButtonOn')
            );
        }

        if (PaymentMethodCode::payPal()->equals($paymentCode)) {
            return new PayPal(
                static::getDataValue($additionalData, 'displayButtonOn')
            );
        }

        return null;
    }
}
