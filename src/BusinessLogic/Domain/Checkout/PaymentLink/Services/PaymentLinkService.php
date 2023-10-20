<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Services;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Factory\PaymentLinkRequestFactory;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Models\PaymentLink;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Models\PaymentLinkRequestContext;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Proxies\PaymentLinkProxy;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Exceptions\InvalidMerchantReferenceException;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Services\TransactionHistoryService;

/**
 * Class PaymentLinkService
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Services
 */
class PaymentLinkService
{
    /**
     * @var PaymentLinkProxy
     */
    private $paymentLinkProxy;

    /**
     * @var PaymentLinkRequestFactory
     */
    private $paymentLinkRequestFactory;

    /**
     * @var TransactionHistoryService
     */
    private $transactionHistoryService;

    /**
     * @param PaymentLinkProxy $paymentLinkProxy
     * @param PaymentLinkRequestFactory $paymentLinkRequestFactory
     * @param TransactionHistoryService $transactionHistoryService
     */
    public function __construct(
        PaymentLinkProxy $paymentLinkProxy,
        PaymentLinkRequestFactory $paymentLinkRequestFactory,
        TransactionHistoryService $transactionHistoryService
    ) {
        $this->paymentLinkProxy = $paymentLinkProxy;
        $this->paymentLinkRequestFactory = $paymentLinkRequestFactory;
        $this->transactionHistoryService = $transactionHistoryService;
    }

    /**
     * @param PaymentLinkRequestContext $context
     *
     * @return PaymentLink
     *
     * @throws InvalidMerchantReferenceException
     */
    public function createPaymentLink(PaymentLinkRequestContext $context): PaymentLink
    {
        $request = $this->paymentLinkRequestFactory->create($context);
        $result = $this->paymentLinkProxy->createPaymentLink($request);
        $transactionHistory = $this->transactionHistoryService->getTransactionHistory($context->getReference());
        $transactionHistory->setPaymentLink($result);
        $this->transactionHistoryService->setTransactionHistory($transactionHistory);

        return $result;
    }
}
