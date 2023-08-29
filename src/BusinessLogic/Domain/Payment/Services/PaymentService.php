<?php

namespace Adyen\Core\BusinessLogic\Domain\Payment\Services;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PaymentMethodCode;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PaymentMethodResponse as CheckoutPaymentMethodResponse;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PaymentMethodsRequest;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Proxies\PaymentsProxy;
use Adyen\Core\BusinessLogic\Domain\Connection\Repositories\ConnectionSettingsRepository;
use Adyen\Core\BusinessLogic\Domain\Payment\Exceptions\FailedToRetrievePaymentMethodsException;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\PaymentMethod;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\PaymentMethodResponse;
use Adyen\Core\BusinessLogic\Domain\Payment\Proxies\PaymentProxy;
use Adyen\Core\BusinessLogic\Domain\Payment\Repositories\PaymentMethodConfigRepository;
use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Adyen\Core\Infrastructure\Logger\Logger;
use Adyen\Core\BusinessLogic\DataAccess\Payment\Entities\PaymentMethod as PaymentMethodEntity;
use Exception;

/**
 * Class PaymentService
 *
 * @package Adyen\Core\BusinessLogic\Domain\Payment\Services
 */
class PaymentService
{
    public const CREDIT_CARD_BRANDS = ['amex', 'bcmc', 'cartebancaire', 'mc', 'visa', 'visadankort'];
    public const CREDIT_CARD_CODE = 'scheme';
    public const ONEY_TYPE = 'facilypay';
    public const ONEY_CODE = 'oney';
    public const ONEY_NAME = 'Oney';

    /**
     * @var PaymentMethodConfigRepository
     */
    protected $repository;
    /**
     * @var ConnectionSettingsRepository
     */
    protected $connectionSettingsRepository;
    /**
     * @var PaymentProxy
     */
    protected $managementProxy;
    /**
     * @var PaymentsProxy
     */
    protected $checkoutProxy;

    /**
     * @param PaymentMethodConfigRepository $repository
     * @param ConnectionSettingsRepository $connectionSettingsRepository
     * @param PaymentProxy $managementProxy
     * @param PaymentsProxy $checkoutProxy
     */
    public function __construct(
        PaymentMethodConfigRepository $repository,
        ConnectionSettingsRepository $connectionSettingsRepository,
        PaymentProxy $managementProxy,
        PaymentsProxy $checkoutProxy
    ) {
        $this->repository = $repository;
        $this->connectionSettingsRepository = $connectionSettingsRepository;
        $this->managementProxy = $managementProxy;
        $this->checkoutProxy = $checkoutProxy;
    }

    /**
     * Retrieves all configured payment methods.
     *
     * @return PaymentMethod[]
     *
     * @throws FailedToRetrievePaymentMethodsException
     * @throws Exception
     */
    public function getConfiguredMethods(): array
    {
        $methods = $this->repository->getConfiguredPaymentMethods();

        $managementMethods = $this->getManagementMethods($this->getMerchantId());

        foreach ($methods as $method) {
            $managementMethod = $this->findManagementMethod($method, $managementMethods);

            if (!$managementMethod) {
                $method->setStatus(false);

                continue;
            }

            $method->setStatus($managementMethod->isEnabled());
            $method->setCountries($managementMethod->getCountries());
            $method->setCurrencies($managementMethod->getCurrencies());
        }

        return $methods;
    }

    /**
     * Retrieves payment method by id.
     *
     * @param string $id
     *
     * @return PaymentMethod|null
     *
     * @throws Exception
     */
    public function getPaymentMethodById(string $id): ?PaymentMethod
    {
        return $this->repository->getPaymentMethodById($id);
    }

    /**
     * Retrieves payment method by code.
     *
     * @param string $code
     *
     * @return PaymentMethod|null
     *
     * @throws Exception
     */
    public function getPaymentMethodByCode(string $code): ?PaymentMethod
    {
        return $this->repository->getPaymentMethodByCode($code);
    }

    /**
     * Saves payment method configuration.
     *
     * @param PaymentMethod $method
     *
     * @return void
     *
     * @throws Exception
     */
    public function saveMethodConfiguration(PaymentMethod $method): void
    {
        $this->repository->saveMethodConfiguration($method);
    }

    /**
     * Updates payment method configuration.
     *
     * @param PaymentMethod $method
     *
     * @return void
     *
     * @throws Exception
     */
    public function updateMethodConfiguration(PaymentMethod $method): void
    {
        $this->repository->updateMethodConfiguration($method);
    }

    /**
     * Deletes payment method configuration by id.
     *
     * @param string $id
     *
     * @return void
     *
     * @throws Exception
     */
    public function deletePaymentMethodById(string $id): void
    {
        $this->repository->deletePaymentMethodById($id);
    }

    /**
     * Retrieves available payment methods.
     *
     * @return PaymentMethodResponse[]
     *
     * @throws FailedToRetrievePaymentMethodsException
     * @throws Exception
     */
    public function getAvailableMethods(): array
    {
        $merchantId = $this->getMerchantId();
        $checkoutMethods = $this->getCheckoutMethods($merchantId);
        $managementMethods = $this->getManagementMethods($merchantId);
        $configuredMethods = $this->repository->getConfiguredPaymentMethods();

        return $this->getFilteredMethods($checkoutMethods, $managementMethods, $configuredMethods);
    }

    /**
     * @param PaymentMethod $method
     * @param PaymentMethodResponse[] $managementMethods
     *
     * @return PaymentMethodResponse|null
     */
    protected function findManagementMethod(PaymentMethod $method, array $managementMethods): ?PaymentMethodResponse
    {
        foreach ($managementMethods as $managementMethod) {
            if ($method->getCode() === $managementMethod->getCode() ||
                ($this->isCardMethod($managementMethod) && $method->getCode() === self::CREDIT_CARD_CODE) ||
                ($this->isOneyMethod($managementMethod) && $method->getCode() === self::ONEY_CODE) ||
                ($this->isWeChatPayMethod($managementMethod) && $method->getCode() === 'wechatpayQR')) {
                return $managementMethod;
            }
        }

        return null;
    }

    /**
     * @param PaymentMethodResponse $method
     *
     * @return bool
     */
    protected function isCardMethod(PaymentMethodResponse $method): bool
    {
        return in_array($method->getCode(), self::CREDIT_CARD_BRANDS, true);
    }

    /**
     * @param PaymentMethodResponse $method
     *
     * @return bool
     */
    protected function isOneyMethod(PaymentMethodResponse $method): bool
    {
        return strpos($method->getCode(), self::ONEY_TYPE) !== false;
    }

    /**
     * @param PaymentMethodResponse $method
     *
     * @return bool
     */
    protected function isWeChatPayMethod(PaymentMethodResponse $method): bool
    {
        return $method->getCode() === 'wechatpay';
    }

    /**
     * @return string
     */
    protected function getMerchantId(): string
    {
        $connectionSettings = $this->connectionSettingsRepository->getConnectionSettings();

        return $connectionSettings ? $connectionSettings->getActiveConnectionData()->getMerchantId() : '';
    }

    /**
     * @param CheckoutPaymentMethodResponse[] $checkoutMethods
     * @param PaymentMethodResponse[] $managementMethods
     * @param PaymentMethod[] $configuredMethods
     *
     * @return PaymentMethodResponse[]
     */
    protected function getFilteredMethods(
        array $checkoutMethods,
        array $managementMethods,
        array $configuredMethods
    ): array {
        $availableMethods = [];
        $oneyConfigured = false;
        $creditCardConfigured = false;

        foreach ($managementMethods as $managementMethod) {
            $checkoutMethod = $this->findCheckoutMethodByType($checkoutMethods, $managementMethod->getCode());

            if (!$checkoutMethod) {
                continue;
            }

            if ($checkoutMethod->getType() === 'wechatpayQR') {
                $managementMethod->setCode('wechatpayQR');
            }

            if ($checkoutMethod->getType() === self::CREDIT_CARD_CODE) {
                $managementMethod->setCode(self::CREDIT_CARD_CODE);
            }

            if (strpos($checkoutMethod->getType(), self::ONEY_TYPE) !== false) {
                $managementMethod->setCode(self::ONEY_CODE);
            }

            if (($checkoutMethod->getType() === self::CREDIT_CARD_CODE && $creditCardConfigured) ||
                $this->isMethodAlreadyConfigured($managementMethod, $configuredMethods) ||
                $this->isMethodAlreadyProcessed($managementMethod, $availableMethods) ||
                (strpos($checkoutMethod->getType(), self::ONEY_TYPE) !== false && $oneyConfigured)) {
                continue;
            }

            if ($checkoutMethod->getType() === self::CREDIT_CARD_CODE) {
                $creditCardConfigured = true;
            }

            if (strpos($checkoutMethod->getType(), self::ONEY_TYPE) !== false) {
                $oneyConfigured = true;
            }

            $managementMethod->setLogo(PaymentMethod::getLogoUrl($managementMethod->getCode()));
            $managementMethod->setType(PaymentMethod::getType($managementMethod->getCode()));
            $managementMethod->setName(
                strpos($checkoutMethod->getType(), self::ONEY_TYPE) !== false ?
                    self::ONEY_NAME : $checkoutMethod->getName()
            );

            $availableMethods[] = $managementMethod;
        }

        return $availableMethods;
    }

    /**
     * @param PaymentMethodResponse $method
     * @param PaymentMethod[] $configuredMethods
     *
     * @return bool
     */
    protected function isMethodAlreadyConfigured(PaymentMethodResponse $method, array $configuredMethods): bool
    {
        foreach ($configuredMethods as $configuredMethod) {
            if ($method->getCode() === $configuredMethod->getCode()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param PaymentMethodResponse $method
     * @param PaymentMethodResponse[] $availableMethods
     * @return bool
     */
    protected function isMethodAlreadyProcessed(PaymentMethodResponse $method, array $availableMethods): bool
    {
        foreach ($availableMethods as $availableMethod) {
            if ($method->getCode() === $availableMethod->getCode()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $merchantId
     *
     * @return CheckoutPaymentMethodResponse[]
     *
     * @throws FailedToRetrievePaymentMethodsException
     */
    protected function getCheckoutMethods(string $merchantId): array
    {
        try {
            $checkoutRequest = new PaymentMethodsRequest(
                $merchantId,
                $this->getAllowedPaymentMethods()
            );
            return $this->checkoutProxy->getAvailablePaymentMethods($checkoutRequest)->getPaymentMethodsResponse();
        } catch (Exception $e) {
            Logger::logError($e->getMessage());

            throw new FailedToRetrievePaymentMethodsException(
                new TranslatableLabel(
                    'Failed to retrieve payment methods from Adyen.',
                    'payments.failedToRetrieveMethodsAdyen'
                ),
                $e
            );
        }
    }

    /**
     * @param string $merchantId
     *
     * @return PaymentMethodResponse[]
     *
     * @throws FailedToRetrievePaymentMethodsException
     */
    protected function getManagementMethods(string $merchantId): array
    {
        try {
            return $this->managementProxy->getAvailablePaymentMethods($merchantId);
        } catch (Exception $e) {
            Logger::logError($e->getMessage());

            throw new FailedToRetrievePaymentMethodsException(
                new TranslatableLabel(
                    'Failed to retrieve payment methods from Adyen.',
                    'payments.failedToRetrieveMethodsAdyen'
                ),
                $e
            );
        }
    }

    /**
     * @param CheckoutPaymentMethodResponse[] $checkoutMethods
     * @param string $type
     *
     * @return CheckoutPaymentMethodResponse|null
     */
    protected function findCheckoutMethodByType(array $checkoutMethods, string $type): ?CheckoutPaymentMethodResponse
    {
        foreach ($checkoutMethods as $method) {
            if (in_array($type, self::CREDIT_CARD_BRANDS) && isset($method->getMetaData()['brands']) &&
                in_array($type, $method->getMetaData()['brands'], true)) {
                return $method;
            }

            if (PaymentMethodCode::isOneyMethod($type) &&
                PaymentMethodCode::isOneyMethod($method->getType())) {
                return $method;
            }

            if ($type === 'wechatpay' && $method->getType() === (string)PaymentMethodCode::weChatPay()) {
                return $method;
            }

            if ($method->getType() === $type) {
                return $method;
            }
        }

        return null;
    }

    /**
     * @return string[]
     */
    protected function getAllowedPaymentMethods(): array
    {
        return PaymentMethodCode::SUPPORTED_PAYMENT_METHODS;
    }
}
