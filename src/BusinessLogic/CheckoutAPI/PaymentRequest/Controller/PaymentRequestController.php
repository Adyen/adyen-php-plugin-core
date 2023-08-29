<?php

namespace Adyen\Core\BusinessLogic\CheckoutAPI\PaymentRequest\Controller;

use Adyen\Core\BusinessLogic\CheckoutAPI\PaymentRequest\Request\StartTransactionRequest;
use Adyen\Core\BusinessLogic\CheckoutAPI\PaymentRequest\Response\StartTransactionResponse;
use Adyen\Core\BusinessLogic\CheckoutAPI\PaymentRequest\Response\UpdatePaymentDetailsResponse;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\InvalidPaymentMethodCodeException;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\DataBag;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PaymentMethodCode;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\StartTransactionRequestContext;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\UpdatePaymentDetailsRequest;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Services\PaymentRequestService;

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

    public function __construct(PaymentRequestService $service)
    {
        $this->service = $service;
    }

    /**
     * @throws InvalidPaymentMethodCodeException
     */
    public function startTransaction(StartTransactionRequest $request): StartTransactionResponse
    {
        return new StartTransactionResponse($this->service->startTransaction(
            new StartTransactionRequestContext(
                PaymentMethodCode::parse($request->getPaymentMethodType()),
                $request->getAmount(),
                $request->getReference(),
                $request->getReturnUrl(),
                new DataBag($request->getStateData()),
                new DataBag($request->getSessionData())
            )
        ));
    }

    public function updatePaymentDetails(array $rawRequest): UpdatePaymentDetailsResponse
    {
        return new UpdatePaymentDetailsResponse(
            $this->service->updatePaymentDetails(UpdatePaymentDetailsRequest::parse($rawRequest))
        );
    }
}
