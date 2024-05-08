<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\Processors;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Factory\PaymentLinkRequestBuilder;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Models\PaymentLinkRequestContext;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\CurrencyMismatchException;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Factory\PaymentRequestBuilder;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\StartTransactionRequestContext;
use Adyen\Core\BusinessLogic\Domain\Checkout\Processors\PaymentLinkRequest\PaymentLinkRequestProcessor;
use Adyen\Core\BusinessLogic\Domain\Checkout\Processors\PaymentRequest\PaymentRequestProcessor;
use Adyen\Core\BusinessLogic\Domain\Integration\Order\OrderService;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Exceptions\InvalidMerchantReferenceException;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Services\TransactionHistoryService;

/**
 * Class PaymentRequestStateDataProcessor
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors
 */
class AmountProcessor implements PaymentRequestProcessor, PaymentLinkRequestProcessor
{
    /**
     * @var TransactionHistoryService
     */
    private $transactionHistoryService;

    /**
     * @var OrderService
     */
    private $orderService;

    /**
     * @param TransactionHistoryService $transactionHistoryService
     * @param OrderService $orderService
     */
    public function __construct(TransactionHistoryService $transactionHistoryService, OrderService $orderService)
    {
        $this->transactionHistoryService = $transactionHistoryService;
        $this->orderService = $orderService;
    }

    /**
     * @param PaymentRequestBuilder $builder
     * @param StartTransactionRequestContext $context
     *
     * @return void
     */
    public function process(PaymentRequestBuilder $builder, StartTransactionRequestContext $context): void
    {
        $builder->setAmount($context->getAmount());
    }

    /**
     * @param PaymentLinkRequestBuilder $builder
     * @param PaymentLinkRequestContext $context
     *
     * @return void
     *
     * @throws InvalidMerchantReferenceException
     * @throws CurrencyMismatchException
     */
    public function processPaymentLink(
        PaymentLinkRequestBuilder $builder,
        PaymentLinkRequestContext $context
    ): void {

        $transactionHistory = $this->transactionHistoryService->getTransactionHistory($context->getReference());
        if($transactionHistory->collection()->isEmpty()){
            $builder->setAmount($context->getAmount());

            return;
        }

        $authorisedAmount = $transactionHistory->getAuthorizedAmount();
        $orderAmount = $this->orderService->getOrderAmount($context->getReference());

        if ($authorisedAmount->getPriceInCurrencyUnits() === $orderAmount->getPriceInCurrencyUnits()) {
            $builder->setAmount($context->getAmount());

            return;
        }

        $builder->setAmount($orderAmount);
    }
}
