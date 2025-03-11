<?php

namespace Adyen\Core\BusinessLogic\CheckoutAPI\PaymentRequest\Controller;

use Adyen\Core\BusinessLogic\CheckoutAPI\PaymentRequest\Request\StartTransactionRequest;
use Adyen\Core\BusinessLogic\CheckoutAPI\PaymentRequest\Response\StartTransactionResponse;
use Adyen\Core\BusinessLogic\CheckoutAPI\PaymentRequest\Response\UpdatePaymentDetailsResponse;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\InvalidCurrencyCode;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\InvalidPaymentMethodCodeException;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\DataBag;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Order;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PaymentMethodCode;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PayPalUpdateOrderRequest;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PayPalUpdateOrderResponse;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\StartTransactionRequestContext;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\UpdatePaymentDetailsRequest;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Services\PaymentRequestService;
use Adyen\Core\BusinessLogic\Domain\PartialPayments\Service\PartialPaymentService;
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
     * @var PartialPaymentService
     */
    private $partialPaymentService;

    /**
     * @param PaymentRequestService $service
     * @param PartialPaymentService $partialPaymentService
     */
    public function __construct(PaymentRequestService $service, PartialPaymentService $partialPaymentService)
    {
        $this->service = $service;
        $this->partialPaymentService = $partialPaymentService;
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
     * @param StartTransactionRequest[] $requests
     * @param Amount $amount
     * @param string $reference
     *
     * @return array
     *
     * @throws InvalidPaymentMethodCodeException
     * @throws Exception
     */
    public function startPartialTransaction(array $requests, Amount $amount, string $reference): array
    {
        $orderCreateResult = $this->partialPaymentService->createOrder($reference, $amount);
        $order = new Order($orderCreateResult->getOrderData(), $orderCreateResult->getPspReference());

        $result = [];

        foreach ($requests as $request) {
            $startTransactionRequest =  new StartTransactionRequestContext(
                PaymentMethodCode::parse($request->getPaymentMethodType()),
                $request->getAmount(),
                $request->getReference(),
                $request->getReturnUrl(),
                new DataBag($request->getStateData()),
                new DataBag($request->getSessionData()),
                $request->getShopperReference()
            );

            $result[] = new StartTransactionResponse(
                $this->service->startTransaction(
                   $startTransactionRequest, $order
                )
            );
        }

        return $result;
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
