<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Proxies;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\AvailablePaymentMethodsResponse;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PaymentMethodsRequest;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PaymentRequest;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\StartTransactionResult;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\UpdatePaymentDetailsRequest;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\UpdatePaymentDetailsResult;
use Exception;

/**
 * Interface PaymentsProxy
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Proxies
 */
interface PaymentsProxy
{
    /**
     * Creates new payment transaction on Adyen system
     *
     * @param PaymentRequest $request
     * @return StartTransactionResult
     */
    public function startPaymentTransaction(PaymentRequest $request): StartTransactionResult;

    /**
     * Updates payment details for started payment transaction as a form of redirect return validation
     *
     * @return UpdatePaymentDetailsResult
     */
    public function updatePaymentDetails(UpdatePaymentDetailsRequest $request): UpdatePaymentDetailsResult;

    /**
     * Retrieves all available payment methods.
     *
     * @param PaymentMethodsRequest $request
     *
     * @return AvailablePaymentMethodsResponse
     *
     * @throws Exception
     */
    public function getAvailablePaymentMethods(PaymentMethodsRequest $request): AvailablePaymentMethodsResponse;
}
