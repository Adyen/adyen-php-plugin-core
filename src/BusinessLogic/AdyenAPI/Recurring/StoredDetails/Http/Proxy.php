<?php

namespace Adyen\Core\BusinessLogic\AdyenAPI\Recurring\StoredDetails\Http;

use Adyen\Core\BusinessLogic\AdyenAPI\Http\Authorized\AuthorizedProxy;
use Adyen\Core\BusinessLogic\AdyenAPI\Recurring\StoredDetails\Requests\DisableStoredDetailsRequest;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\ShopperReference;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Proxies\StoredDetailsProxy;
use Adyen\Core\Infrastructure\Http\Exceptions\HttpRequestException;
use Adyen\Core\Infrastructure\Logger\Logger;

/**
 * Class Proxy
 *
 * @package Adyen\Core\BusinessLogic\AdyenAPI\Recurring\StoredDetails\Http
 */
class Proxy extends AuthorizedProxy implements StoredDetailsProxy
{
    /**
     * @inheritDoc
     */
    public function disable(ShopperReference $shopperReference, string $detailReference, string $merchant): void
    {
        try {
            $this->post(new DisableStoredDetailsRequest($shopperReference, $detailReference, $merchant))
                ->decodeBodyToArray();
        } catch (HttpRequestException $e) {
            Logger::logError('Failed to disable stored payment details with reference ' . $detailReference .
                ' because ' . $e->getMessage());

            throw $e;
        }
    }
}
