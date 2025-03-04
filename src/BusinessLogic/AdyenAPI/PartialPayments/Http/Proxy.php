<?php

namespace Adyen\Core\BusinessLogic\AdyenAPI\PartialPayments\Http;

use Adyen\Core\BusinessLogic\AdyenAPI\Http\Authorized\AuthorizedProxy;
use Adyen\Core\BusinessLogic\AdyenAPI\PartialPayments\Requests\BalanceHttpRequest;
use Adyen\Core\BusinessLogic\AdyenAPI\PartialPayments\Requests\OrderCancelHttpRequest;
use Adyen\Core\BusinessLogic\AdyenAPI\PartialPayments\Requests\OrderCreateHttpRequest;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\InvalidCurrencyCode;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Currency;
use Adyen\Core\BusinessLogic\Domain\PartialPayments\Models\BalanceRequest;
use Adyen\Core\BusinessLogic\Domain\PartialPayments\Models\BalanceResult;
use Adyen\Core\BusinessLogic\Domain\PartialPayments\Models\OrderCancelRequest;
use Adyen\Core\BusinessLogic\Domain\PartialPayments\Models\OrderCancelResult;
use Adyen\Core\BusinessLogic\Domain\PartialPayments\Models\OrderCreateRequest;
use Adyen\Core\BusinessLogic\Domain\PartialPayments\Models\OrderCreateResult;
use Adyen\Core\BusinessLogic\Domain\PartialPayments\Proxies\PartialPaymentProxy;
use Adyen\Core\Infrastructure\Http\Exceptions\HttpRequestException;

/**
 * Class Proxy
 *
 * @package Adyen\Core\BusinessLogic\AdyenAPI\PartialPayments\Http
 */
class Proxy extends AuthorizedProxy implements PartialPaymentProxy
{
    /**
     * @inheritDoc
     *
     * @throws HttpRequestException
     * @throws InvalidCurrencyCode
     */
    public function getBalance(BalanceRequest $balanceRequest): BalanceResult
    {
        $response = $this->post(new BalanceHttpRequest($balanceRequest))->decodeBodyToArray();

        return new BalanceResult(
            $response['pspReference'] ?? '',
            $response['resultCode'] ?? '',
            isset($response['balance']) ?
                Amount::fromInt($response['balance']['value'], Currency::fromIsoCode($response['balance']['currency'])) :
                null
        );
    }

    /**
     * @inheritDoc
     *
     * @throws HttpRequestException
     * @throws InvalidCurrencyCode
     */
    public function createOrder(OrderCreateRequest $orderCreateRequest): OrderCreateResult
    {
        $response = $this->post(new OrderCreateHttpRequest($orderCreateRequest))->decodeBodyToArray();

        return new OrderCreateResult(
            isset($response['amount']) ?
                Amount::fromInt($response['amount']['value'], Currency::fromIsoCode($response['amount']['currency'])) :
                null,
            $result['expiresAt'] ?? '',
            $result['orderData'] ?? '',
            $result['pspReference'] ?? '',
            $result['reference'] ?? '',
            isset($response['remainingAmount']) ?
                Amount::fromInt($response['remainingAmount']['value'], Currency::fromIsoCode($response['remainingAmount']['currency'])) :
                null
        );
    }

    /**
     * @inheritDoc
     *
     * @throws HttpRequestException
     */
    public function cancelOrder(OrderCancelRequest $orderCancelRequest): OrderCancelResult
    {
        $response = $this->post(new OrderCancelHttpRequest($orderCancelRequest))->decodeBodyToArray();

        return new OrderCancelResult(
            $response['pspReference'] ?? '',
            $response['resultCode'] ?? ''
        );
    }
}
