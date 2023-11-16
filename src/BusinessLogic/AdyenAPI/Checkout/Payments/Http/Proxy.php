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
use Adyen\Core\BusinessLogic\Domain\Payment\Services\PaymentService;
use Adyen\Core\Infrastructure\Http\Exceptions\HttpRequestException;

/**
 * Class Proxy
 *
 * @package Adyen\Core\BusinessLogic\AdyenAPI\Checkout\Payments\Http
 */
class Proxy extends AuthorizedProxy implements PaymentsProxy
{

    /**
     * @param PaymentRequest $request
     *
     * @return StartTransactionResult
     *
     * @throws HttpRequestException
     */
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

    /**
     * @param UpdatePaymentDetailsRequest $request
     *
     * @return UpdatePaymentDetailsResult
     *
     * @throws HttpRequestException
     */
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

    /**
     * @param PaymentMethodsRequest $request
     *
     * @return AvailablePaymentMethodsResponse
     *
     * @throws HttpRequestException
     */
    public function getAvailablePaymentMethods(PaymentMethodsRequest $request): AvailablePaymentMethodsResponse
    {
        $response = $this->post(new PaymentMethodsHttpRequest($request))->decodeBodyToArray();

        return new AvailablePaymentMethodsResponse(
            $this->filterOnlyAvailablePaymentMethods(
                $this->transformPaymentMethodsResponse($response['paymentMethods'] ?? []),
                $request
            ),
            $this->transformStoredCreditCardsResponse($response['storedPaymentMethods'] ?? [])
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
     * Return only credit cards from response, since all other stored methods are fetched through /listRecurringDetails request.
     *
     * @param array $response
     *
     * @return array
     */
    private function transformStoredCreditCardsResponse(array $response): array
    {
        return array_values(array_map(static function (array $method) {
            $type = $method['type'] ?? '';
            $brand = $method['brand'] ?? '';

            return new PaymentMethodResponse(
                $method['name'] ?? '',
                PaymentMethodCode::isGiftCard($type) ? $brand : $type,
                $method
            );
        }, array_filter($response, static function (array $method) {
            return $method['type'] === PaymentService::CREDIT_CARD_CODE;
        })));
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
