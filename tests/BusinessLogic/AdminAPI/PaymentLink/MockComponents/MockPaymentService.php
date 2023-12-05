<?php

namespace Adyen\Core\Tests\BusinessLogic\AdminAPI\PaymentLink\MockComponents;

use Adyen\Core\BusinessLogic\Domain\Payment\Models\PaymentMethod;
use Adyen\Core\BusinessLogic\Domain\Payment\Services\PaymentService;

/**
 * Class MockPaymentService
 *
 * @package Adyen\Core\Tests\BusinessLogic\AdminAPI\PaymentLink\MockComponents
 */
class MockPaymentService extends PaymentService
{
    /**
     * @var PaymentMethod[]
     */
    private $paymentMethods = [];

    /**
     * @return PaymentMethod[]
     */
    public function getConfiguredMethods(): array
    {
        return $this->paymentMethods;
    }

    /**
     * @param array $methods
     *
     * @return void
     */
    public function setPaymentMethods(array $methods): void
    {
        $this->paymentMethods = $methods;
    }
}
