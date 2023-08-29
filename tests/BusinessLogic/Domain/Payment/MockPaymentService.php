<?php

namespace Adyen\Core\Tests\BusinessLogic\Domain\Payment;

use Adyen\Core\BusinessLogic\Domain\Payment\Services\PaymentService;
use Exception;

class MockPaymentService extends PaymentService
{
    public $getConfiguredPaymentMethodsFails = false;

    public function getConfiguredMethods(): array
    {
        if ($this->getConfiguredPaymentMethodsFails) {
            throw new Exception('Get configured payment methods fails.');
        }

        return parent::getConfiguredMethods();
    }
}
