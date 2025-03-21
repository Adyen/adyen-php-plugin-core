<?php

namespace Adyen\Core\BusinessLogic\CheckoutAPI\PartialPayment\Controller;

use Adyen\Core\BusinessLogic\CheckoutAPI\PartialPayment\Request\BalanceCheckRequest;
use Adyen\Core\BusinessLogic\CheckoutAPI\PartialPayment\Request\StartPartialTransactionsRequest;
use Adyen\Core\BusinessLogic\CheckoutAPI\PartialPayment\Response\BalanceCheckResponse;
use Adyen\Core\BusinessLogic\CheckoutAPI\PartialPayment\Response\StartPartialTransactionsResponse;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\InvalidCurrencyCode;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\InvalidPaymentMethodCodeException;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Currency;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Services\PaymentRequestService;
use Adyen\Core\BusinessLogic\Domain\PartialPayments\Service\PartialPaymentService;

/**
 * Class PartialPaymentController
 *
 * @package Adyen\Core\BusinessLogic\CheckoutAPI\PartialPayment\Controller
 */
class PartialPaymentController
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
     * @param PartialPaymentService $partialPaymentService
     */
    public function __construct(PaymentRequestService $service, PartialPaymentService $partialPaymentService)
    {
        $this->service = $service;
        $this->partialPaymentService = $partialPaymentService;
    }

    /**
     * @param BalanceCheckRequest $balanceCheckRequest
     *
     * @return BalanceCheckResponse
     *
     * @throws InvalidCurrencyCode
     */
    public function checkBalance(BalanceCheckRequest $balanceCheckRequest): BalanceCheckResponse
    {
        return new BalanceCheckResponse($this->partialPaymentService->checkBalance(
            Amount::fromFloat(
                $balanceCheckRequest->getAmount(),
                Currency::fromIsoCode($balanceCheckRequest->getCurrency()
                )
            ),
            $balanceCheckRequest->getPaymentMethod()
        ));
    }

    /**
     * Handles partial payments.
     *
     * @param StartPartialTransactionsRequest $partialTransactionRequest
     * @return StartPartialTransactionsResponse
     * @throws InvalidCurrencyCode
     * @throws InvalidPaymentMethodCodeException
     */
    public function startPartialTransactions(
        StartPartialTransactionsRequest $partialTransactionRequest
    ): StartPartialTransactionsResponse
    {
        return new StartPartialTransactionsResponse(
            $this->service->startPartialTransactions(
                $partialTransactionRequest
            )
        );
    }
}
