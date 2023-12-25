<?php

namespace Adyen\Core\BusinessLogic\AdyenAPI\Recurring\StoredDetails\Http;

use Adyen\Core\BusinessLogic\AdyenAPI\Http\Authorized\AuthorizedProxy;
use Adyen\Core\BusinessLogic\AdyenAPI\Recurring\StoredDetails\Requests\DisableStoredDetailsRequest;
use Adyen\Core\BusinessLogic\AdyenAPI\Recurring\StoredDetails\Requests\StoredPaymentDetailsHttpRequest;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PaymentMethodResponse;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\ShopperReference;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Proxies\StoredDetailsProxy;
use Adyen\Core\BusinessLogic\Domain\Payment\Services\PaymentService;
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
            Logger::logError(
                'Failed to disable stored payment details with reference ' . $detailReference .
                ' because ' . $e->getMessage()
            );

            throw $e;
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
        try {
            $response = $this->post(
                new StoredPaymentDetailsHttpRequest($shopperReference, $merchant)
            )->decodeBodyToArray();

            return $this->transformStoredPaymentDetailsResponse($response['details'] ?? []);
        } catch (HttpRequestException $e) {
            Logger::logError(
                'Failed to retrieve stored payment details for shopper with reference ' . $shopperReference .
                ' because ' . $e->getMessage()
            );

            return [];
        }
    }

    /**
     * Transform response to array of PaymentMethodResponse objects.
     * Exclude credit cards methods, since they are fetched from other endpoint.
     *
     * @param array $response
     *
     * @return PaymentMethodResponse[]
     */
    private function transformStoredPaymentDetailsResponse(array $response): array
    {
        $response = array_filter($response, static function (array $method) {
            return !isset($method['RecurringDetail']['variant']) || !in_array(
                    $method['RecurringDetail']['variant'],
                    PaymentService::CREDIT_CARD_BRANDS
                );
        });

        return array_map(static function (array $method) {
            return new PaymentMethodResponse(
                $method['RecurringDetail']['variant'] ?? '',
                $method['RecurringDetail']['variant'] ?? '',
                $method
            );
        }, $response);
    }
}
