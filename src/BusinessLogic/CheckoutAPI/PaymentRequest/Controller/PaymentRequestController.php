<?php

namespace Adyen\Core\BusinessLogic\CheckoutAPI\PaymentRequest\Controller;

use Adyen\Core\BusinessLogic\CheckoutAPI\PaymentRequest\Request\StartTransactionRequest;
use Adyen\Core\BusinessLogic\CheckoutAPI\PaymentRequest\Response\StartTransactionResponse;
use Adyen\Core\BusinessLogic\CheckoutAPI\PaymentRequest\Response\UpdatePaymentDetailsResponse;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\InvalidCurrencyCode;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\InvalidPaymentMethodCodeException;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\DataBag;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PaymentMethodCode;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PayPalUpdateOrderRequest;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PayPalUpdateOrderResponse;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\StartTransactionRequestContext;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\UpdatePaymentDetailsRequest;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Services\PaymentRequestService;
use Exception;

/**
 * Class PaymentRequestController
 *
 * @package Adyen\Core\BusinessLogic\CheckoutAPI\PaymentRequest\Controller
 */
class PaymentRequestController
{
    /**
     * @var PaymentRequestService
     */
    private $service;

    /**
     * @param PaymentRequestService $service
     */
    public function __construct(PaymentRequestService $service)
    {
        $this->service = $service;
    }

    /**
     * @throws InvalidPaymentMethodCodeException
     * @throws Exception
     */
    public function startTransaction(StartTransactionRequest $request): StartTransactionResponse
    {
        return new StartTransactionResponse(
            $this->service->startTransaction(
                new StartTransactionRequestContext(
                    PaymentMethodCode::parse($request->getPaymentMethodType()),
                    $request->getAmount(),
                    $request->getReference(),
                    $request->getReturnUrl(),
                    new DataBag($request->getStateData()),
                    new DataBag($request->getSessionData()),
                    $request->getShopperReference()
                )
            )
        );
    }

    /**
     * @param array $rawRequest
     *
     * @return UpdatePaymentDetailsResponse
     *
     * @throws Exception
     */
    public function updatePaymentDetails(array $rawRequest): UpdatePaymentDetailsResponse
    {
        return new UpdatePaymentDetailsResponse(
            $this->service->updatePaymentDetails(UpdatePaymentDetailsRequest::parse($rawRequest))
        );
    }

    /**
     * @param array $rawRequest
     *
     * @return PayPalUpdateOrderResponse
     *
     * @throws InvalidCurrencyCode
     */
    public function paypalUpdateOrder(array $rawRequest): PaypalUpdateOrderResponse
    {
        return $this->service->paypalUpdateOrder(PayPalUpdateOrderRequest::parse($rawRequest));
    }
}
