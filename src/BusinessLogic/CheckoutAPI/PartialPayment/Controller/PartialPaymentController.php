<?php

namespace Adyen\Core\BusinessLogic\CheckoutAPI\PartialPayment\Controller;

use Adyen\Core\BusinessLogic\CheckoutAPI\PartialPayment\Request\BalanceCheckRequest;
use Adyen\Core\BusinessLogic\CheckoutAPI\PartialPayment\Response\BalanceCheckResponse;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\InvalidCurrencyCode;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Currency;
use Adyen\Core\BusinessLogic\Domain\PartialPayments\Models\BalanceResult;
use Adyen\Core\BusinessLogic\Domain\PartialPayments\Service\PartialPaymentService;

/**
 * Class PartialPaymentController
 *
 * @package Adyen\Core\BusinessLogic\CheckoutAPI\PartialPayment\Controller
 */
class PartialPaymentController
{
    /**
     * @var PartialPaymentService
     */
    private $partialPaymentService;

    /**
     * @param PartialPaymentService $partialPaymentService
     */
    public function __construct(PartialPaymentService $partialPaymentService)
    {
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
}
