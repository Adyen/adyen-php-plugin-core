<?php

namespace Adyen\Core\BusinessLogic\Domain\Integration\Payment;

use Adyen\Core\BusinessLogic\Domain\Payment\Models\PaymentMethod;

/**
 * Class ShopPaymentService
 *
 * @package Adyen\Core\BusinessLogic\Domain\Payment\Interfaces
 */
interface ShopPaymentService
{
    /**
     * Creates new payment method in the shop system.
     *
     * @param PaymentMethod $method
     *
     * @return void
     */
    public function createPaymentMethod(PaymentMethod $method): void;

    /**
     * Update existing payment method in the shop system.
     *
     * @param PaymentMethod $method
     *
     * @return void
     */
    public function updatePaymentMethod(PaymentMethod $method): void;

    /**
     * Delete existing payment method from shop system.
     *
     * @param string $methodId
     *
     * @return void
     */
    public function deletePaymentMethod(string $methodId): void;

    /**
     * Deletes all payment methods from shop system.
     *
     * @return void
     */
    public function deleteAllPaymentMethods(): void;
}
