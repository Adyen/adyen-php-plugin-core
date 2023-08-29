<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Proxies;

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
}
