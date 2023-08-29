<?php

namespace Adyen\Core\BusinessLogic\AdyenAPI\Management\Payment\Http;

use Adyen\Core\BusinessLogic\AdyenAPI\Http\Authorized\AuthorizedProxy;
use Adyen\Core\BusinessLogic\AdyenAPI\Http\Requests\HttpRequest;
use Adyen\Core\BusinessLogic\Domain\Payment\Exceptions\PaymentMethodRequestDataEmptyException;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\PaymentMethodResponse;
use Adyen\Core\BusinessLogic\Domain\Payment\Proxies\PaymentProxy;
use Adyen\Core\Infrastructure\Http\Exceptions\HttpRequestException;

/**
 * Class Proxy
 *
 * @package Adyen\Core\BusinessLogic\AdyenAPI\Management\Payment\Http
 */
class Proxy extends AuthorizedProxy implements PaymentProxy
{
    /**
     * @inheritDoc
     */
    public function getAvailablePaymentMethods(string $merchantId): array
    {
        $page = 1;
        $response = $this->getMethods($merchantId, $page);
        $result = $this->transformPaymentMethods($response['data'] ?? []);

        while (!empty($response['data'])) {
            $page++;
            $response = $this->getMethods($merchantId, $page);
            /** @noinspection SlowArrayOperationsInLoopInspection */
            $result = array_merge($result, $this->transformPaymentMethods($response['data']));
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethodById(string $merchantId, string $methodId): ?PaymentMethodResponse
    {
        $request = new HttpRequest("/merchants/$merchantId/paymentMethodSettings/$methodId");
        $response = $this->get($request)->decodeBodyToArray();

        return new PaymentMethodResponse(
            $response['id'] ?? '',
                $response['type'] ?? '',
                $response['enabled'] ?? false,
                !empty($paymentMethod['countries']) ? $paymentMethod['countries'] : ['ANY'],
                !empty($paymentMethod['currencies']) ? $paymentMethod['currencies'] : ['ANY']
        );
    }

    /**
     * @param string $merchantId
     * @param int $page
     *
     * @return array
     *
     * @throws HttpRequestException
     */
    protected function getMethods(string $merchantId, int $page): array
    {
        $request = new HttpRequest(
            "/merchants/$merchantId/paymentMethodSettings",
            [],
            [
                'pageNumber' => $page,
                'pageSize' => 100
            ]
        );

        return $this->get($request)->decodeBodyToArray();
    }

    /**
     * @param $response
     *
     * @return PaymentMethodResponse[]
     *
     * @throws PaymentMethodRequestDataEmptyException
     */
    protected function transformPaymentMethods($response): array
    {
        $result = [];

        foreach ($response as $method) {
            $paymentMethod = $method['PaymentMethod'] ?? [];
            $result[] = new PaymentMethodResponse(
                $paymentMethod['id'] ?? '',
                $paymentMethod['type'] ?? '',
                $paymentMethod['enabled'] ?? false,
                    !empty($paymentMethod['countries']) ? $paymentMethod['countries'] : ['ANY'],
                    !empty($paymentMethod['currencies']) ? $paymentMethod['currencies'] : ['ANY']
            );
        }

        return $result;
    }
}
