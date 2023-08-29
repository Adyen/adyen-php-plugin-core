<?php

namespace Adyen\Core\Tests\BusinessLogic\Common\MockComponents;

use Adyen\Core\BusinessLogic\Domain\Integration\Payment\ShopPaymentService;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\PaymentMethod;

class MockShopPaymentService implements ShopPaymentService
{

    /**
     * @inheritDoc
     */
    public function createPaymentMethod(PaymentMethod $method): void
    {
    }

    /**
     * @inheritDoc
     */
    public function updatePaymentMethod(PaymentMethod $method): void
    {
    }

    /**
     * @inheritDoc
     */
    public function deletePaymentMethod(string $methodId): void
    {
    }

    /**
     * @inheritDoc
     */
    public function deleteAllPaymentMethods(): void
    {
    }
}
