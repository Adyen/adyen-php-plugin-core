<?php

namespace Adyen\Core\Tests\BusinessLogic\CheckoutAPI\CheckoutConfig\MockComponents;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\ShopperReference;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Proxies\StoredDetailsProxy;
use Adyen\Core\Infrastructure\Http\Exceptions\HttpRequestException;

class MockStoredDetailsProxy implements StoredDetailsProxy
{
    public $isSuccessful = true;

    public function disable(ShopperReference $shopperReference, string $detailReference, string $merchant): void
    {
        if (!$this->isSuccessful) {
            throw new HttpRequestException('Exception');
        }
    }
}
