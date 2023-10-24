<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Services;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Factory\PaymentLinkRequestFactory;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Models\PaymentLink;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Models\PaymentLinkRequestContext;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Proxies\PaymentLinkProxy;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\ShopEvents;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Exceptions\InvalidMerchantReferenceException;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models\HistoryItem;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Services\TransactionHistoryService;
use Adyen\Core\Infrastructure\Utility\TimeProvider;
use Adyen\Webhook\PaymentStates;
use DateTimeInterface;

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
        $paymentLink = $this->paymentLinkProxy->createPaymentLink($request);
        $transactionHistory = $this->transactionHistoryService->getTransactionHistory($context->getReference());
        $this->addHistoryItem($transactionHistory, $context->getAmount(), $paymentLink);

        return $paymentLink;
    }

    /**
     * Adds new history item to collection.
     *
     * @param TransactionHistory $history
     * @param Amount $amount
     * @param PaymentLink $paymentLink
     *
     * @return void
     *
     * @throws InvalidMerchantReferenceException
     */
    private function addHistoryItem(TransactionHistory $history, Amount $amount, PaymentLink $paymentLink): void
    {
        $lastItem = $history->collection()->last();
        $paymentLinkCount = count(
            $history->collection()->filterByEventCode(ShopEvents::PAYMENT_LINK_CREATED)->getAll()
        );
        $history->add(
            new HistoryItem(
                'payment_link' . ++$paymentLinkCount . '_' . $history->getOriginalPspReference(),
                $history->getMerchantReference(),
                ShopEvents::PAYMENT_LINK_CREATED,
                $lastItem ? $lastItem->getPaymentState() : PaymentStates::STATE_NEW,
                TimeProvider::getInstance()->getCurrentLocalTime()->format(DateTimeInterface::ATOM),
                true,
                $amount,
                'Payment link',
                $history->getRiskScore() ?? 0,
                $history->isLive() ?? true
            )
        );

        $history->setPaymentLink($paymentLink);
        $this->transactionHistoryService->setTransactionHistory($history);
    }
}
