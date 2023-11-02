<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Proxies;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PaymentMethodResponse;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\ShopperReference;
use Exception;

/**
 * Interface StoredDetailsProxy
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Proxies
 */
interface StoredDetailsProxy
{
    /**
     * Disable stored payment details.
     *
     * @param ShopperReference $shopperReference
     * @param string $detailReference
     * @param string $merchant
     *
     * @return void
     *
     * @throws Exception
     */
    public function disable(ShopperReference $shopperReference, string $detailReference, string $merchant): void;

    /**
     * Gets list of the stored payment details for a shopper, if there are any available.
     *
     * @param ShopperReference $shopperReference
     * @param string $merchant
     *
     * @return PaymentMethodResponse[]
     *
     * @throws Exception
     */
    public function getStoredPaymentDetails(ShopperReference $shopperReference, string $merchant): array;
}
