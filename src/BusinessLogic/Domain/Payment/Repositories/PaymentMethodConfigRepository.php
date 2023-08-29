<?php

namespace Adyen\Core\BusinessLogic\Domain\Payment\Repositories;

use Adyen\Core\BusinessLogic\Domain\Payment\Models\PaymentMethod;
use Adyen\Core\BusinessLogic\DataAccess\Payment\Entities\PaymentMethod as PaymentMethodEntity;
use Exception;

/**
 * Class PaymentMethodConfigRepository
 *
 * @package Adyen\Core\BusinessLogic\Domain\Payment\Repositories
 */
interface PaymentMethodConfigRepository
{
    /**
     * Retrieves all configured payment methods.
     *
     * @return PaymentMethod[]
     *
     * @throws Exception
     */
    public function getConfiguredPaymentMethods(): array;

    /**
     * Retrieves all configured payment methods for all shops.
     *
     * @return PaymentMethod[]
     *
     * @throws Exception
     */
    public function getConfiguredPaymentMethodsForAllShops(): array;

    /**
     * Retrieves all configured payment methods entities for all shops.
     *
     * @return PaymentMethodEntity[]
     *
     * @throws Exception
     */
    public function getConfiguredPaymentMethodsEntities(): array;

    /**
     * Retrieves all express checkout payment methods that have express checkout feature enabled.
     *
     * @return PaymentMethod[] Enabled express checkout payment methods
     *
     * @throws Exception
     */
    public function getEnabledExpressCheckoutPaymentMethods(): array;

    /**
     * Retrieves configured payment method by id.
     *
     * @param string $id
     *
     * @return PaymentMethod|null
     *
     * @throws Exception
     */
    public function getPaymentMethodById(string $id): ?PaymentMethod;

    /**
     * Retrieves configured payment method by code.
     *
     * @param string $code
     *
     * @return PaymentMethod|null
     *
     * @throws Exception
     */
    public function getPaymentMethodByCode(string $code): ?PaymentMethod;

    /**
     * Saves payment method configuration.
     *
     * @param PaymentMethod $method
     *
     * @return void
     *
     * @throws Exception
     */
    public function saveMethodConfiguration(PaymentMethod $method): void;

    /**
     * Updates payment method configuration.
     *
     * @param PaymentMethod $method
     *
     * @return void
     *
     * @throws Exception
     */
    public function updateMethodConfiguration(PaymentMethod $method): void;

    /**
     * Deletes payment method configuration by id.
     *
     * @param string $id
     *
     * @return void
     *
     * @throws Exception
     */
    public function deletePaymentMethodById(string $id): void;

    /**
     * Deletes all configured payment methods.
     *
     * @return void
     *
     * @throws Exception
     */
    public function deleteConfiguredMethods(): void;
}
