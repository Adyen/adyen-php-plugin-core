<?php

namespace Adyen\Core\BusinessLogic\Domain\Payment\Proxies;

use Adyen\Core\BusinessLogic\Domain\Payment\Models\PaymentMethodResponse;
use Exception;

/**
 * Class PaymentProxy
 *
 * @package Adyen\Core\BusinessLogic\Domain\Payment\Proxies
 */
interface PaymentProxy
{
    /**
     * Retrieves all available payment methods.
     *
     * @param string $merchantId
     *
     * @return PaymentMethodResponse[]
     *
     * @throws Exception
     */
    public function getAvailablePaymentMethods(string $merchantId): array;

    /**
     * Retrieves payment method by id.
     *
     * @param string $merchantId
     * @param string $methodId
     *
     * @return PaymentMethodResponse|null
     *
     * @throws Exception
     */
    public function getPaymentMethodById(string $merchantId, string $methodId): ?PaymentMethodResponse;
}
