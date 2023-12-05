<?php

namespace Adyen\Core\Tests\BusinessLogic\CheckoutAPI\CheckoutConfig\MockComponents;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PaymentMethodResponse;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\ShopperReference;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Proxies\StoredDetailsProxy;
use Adyen\Core\Infrastructure\Http\Exceptions\HttpRequestException;

class MockStoredDetailsProxy implements StoredDetailsProxy
{
    public $isSuccessful = true;

    /**
     * @var PaymentMethodResponse[]
     */
    private $storedPayments = [];

    /**
     * @param ShopperReference $shopperReference
     * @param string $detailReference
     * @param string $merchant
     *
     * @return void
     *
     * @throws HttpRequestException
     */
    public function disable(ShopperReference $shopperReference, string $detailReference, string $merchant): void
    {
        if (!$this->isSuccessful) {
            throw new HttpRequestException('Exception');
        }
    }

    /**
     * @param ShopperReference $shopperReference
     * @param string $merchant
     *
     * @return PaymentMethodResponse[]
     */
    public function getStoredPaymentDetails(ShopperReference $shopperReference, string $merchant): array
    {
        return $this->storedPayments;
    }

    /**
     * @param PaymentMethodResponse[] $details
     *
     * @return void
     */
    public function setStoredPaymentDetails(array $details): void
    {
        $this->storedPayments = $details;
    }
}
