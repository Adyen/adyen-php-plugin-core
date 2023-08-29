<?php

namespace Adyen\Core\BusinessLogic\AdyenAPI\Checkout\Payments\Http;

use Adyen\Core\BusinessLogic\AdyenAPI\Checkout\Payments\Requests\PaymentHttpRequest;
use Adyen\Core\BusinessLogic\AdyenAPI\Checkout\Payments\Requests\PaymentMethodsHttpRequest;
use Adyen\Core\BusinessLogic\AdyenAPI\Checkout\Payments\Requests\UpdatePaymentDetailsHttpRequest;
use Adyen\Core\BusinessLogic\AdyenAPI\Http\Authorized\AuthorizedProxy;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\AvailablePaymentMethodsResponse;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PaymentMethodCode;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PaymentMethodResponse;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PaymentMethodsRequest;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PaymentRequest;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\ResultCode;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\StartTransactionResult;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\UpdatePaymentDetailsRequest;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\UpdatePaymentDetailsResult;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Proxies\PaymentsProxy;

/**
 * Class Proxy
 *
 * @package Adyen\Core\BusinessLogic\AdyenAPI\Checkout\Payments\Http
 */
class Proxy extends AuthorizedProxy implements PaymentsProxy
{

    public function startPaymentTransaction(PaymentRequest $request): StartTransactionResult
    {
        $response = $this->post(new PaymentHttpRequest($request))->decodeBodyToArray();

        return new StartTransactionResult(
            ResultCode::parse($response['resultCode']),
            $response['pspReference'] ?? null,
            $response['action'] ?? null,
            $response['donationToken'] ?? ''
        );
    }

    public function updatePaymentDetails(UpdatePaymentDetailsRequest $request): UpdatePaymentDetailsResult
    {
        $response = $this->post(new UpdatePaymentDetailsHttpRequest($request))->decodeBodyToArray();

        return new UpdatePaymentDetailsResult(
            ResultCode::parse($response['resultCode']),
            $response['pspReference'] ?? null,
            $response['donationToken'] ?? '',
            $response['merchantReference'] ?? '',
            $response['paymentMethod']['type'] ?? ''
        );
    }

    public function getAvailablePaymentMethods(PaymentMethodsRequest $request): AvailablePaymentMethodsResponse
    {
        $response = $this->post(new PaymentMethodsHttpRequest($request))->decodeBodyToArray();

        return new AvailablePaymentMethodsResponse(
            $this->filterOnlyAvailablePaymentMethods(
                $this->transformPaymentMethodsResponse($response['paymentMethods'] ?? []),
                $request
            ),
            $this->transformPaymentMethodsResponse($response['storedPaymentMethods'] ?? [])
        );
    }

    /**
     * @param array $response
     *
     * @return array
     */
    private function transformPaymentMethodsResponse(array $response): array
    {
        return array_map(static function(array $method) {
            $type = $method['type'] ?? '';
            $brand = $method['brand'] ?? '';

            return new PaymentMethodResponse(
                $method['name'] ?? '',
                PaymentMethodCode::isGiftCard($type) ? $brand : $type,
                $method
            );
        }, $response);
    }

    /**
     * @param PaymentMethodResponse[] $paymentMethodsResponse
     * @param PaymentMethodsRequest $request
     * @return PaymentMethodResponse[]
     */
    private function filterOnlyAvailablePaymentMethods(array $paymentMethodsResponse, PaymentMethodsRequest $request): array
    {
        return array_values(array_filter(array_map(static function (PaymentMethodResponse $methodResponse) use ($request) {
            return in_array($methodResponse->getType(), $request->getAllowedPaymentMethods()) ? $methodResponse : null;
        }, $paymentMethodsResponse)));
    }
}
