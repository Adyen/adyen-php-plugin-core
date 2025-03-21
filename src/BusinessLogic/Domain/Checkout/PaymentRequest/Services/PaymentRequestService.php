<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Services;

use Adyen\Core\BusinessLogic\CheckoutAPI\PartialPayment\Request\StartPartialTransactionsRequest;
use Adyen\Core\BusinessLogic\CheckoutAPI\PaymentRequest\Request\StartTransactionRequest;
use Adyen\Core\BusinessLogic\CheckoutAPI\PaymentRequest\Response\StartTransactionResponse;
use Adyen\Core\BusinessLogic\Domain\Checkout\AdyenGiving\Models\DonationsData;
use Adyen\Core\BusinessLogic\Domain\Checkout\AdyenGiving\Repositories\DonationsDataRepository;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\InvalidCurrencyCode;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\InvalidPaymentMethodCodeException;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Factory\PaymentRequestFactory;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Currency;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\DataBag;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PaymentMethodCode;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PayPalUpdateOrderRequest;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PayPalUpdateOrderResponse;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\StartTransactionRequestContext;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\StartTransactionResult;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\UpdatePaymentDetailsRequest;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\UpdatePaymentDetailsResult;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Proxies\PaymentsProxy;
use Adyen\Core\BusinessLogic\Domain\Connection\Enums\Mode;
use Adyen\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Models\CaptureType;
use Adyen\Core\BusinessLogic\Domain\PartialPayments\Models\Order;
use Adyen\Core\BusinessLogic\Domain\PartialPayments\Service\PartialPaymentService;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\AuthorizationType;
use Adyen\Core\BusinessLogic\Domain\Payment\Repositories\PaymentMethodConfigRepository;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Exceptions\InvalidMerchantReferenceException;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models\HistoryItem;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Services\TransactionHistoryService;
use Exception;

/**
 * Class PaymentRequestService
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Services
 */
class PaymentRequestService
{
    private const METHODS_WITH_DONATIONS = ['scheme', 'ideal'];
    /**
     * @var PaymentsProxy
     */
    private $paymentsProxy;
    /**
     * @var PaymentRequestFactory
     */
    private $paymentRequestFactory;
    /**
     * @var DonationsDataRepository
     */
    private $donationsDataRepository;

    /**
     * @var TransactionHistoryService
     */
    private $transactionHistoryService;

    /**
     * @var PaymentMethodConfigRepository
     */
    private $methodConfigRepository;
    /**
     * @var ConnectionService
     */
    private $connectionService;
    /**
     * @var PartialPaymentService
     */
    private $partialPaymentsService;

    /**
     * @param PaymentsProxy $paymentsProxy
     * @param PaymentRequestFactory $paymentRequestFactory
     * @param DonationsDataRepository $donationsDataRepository
     * @param TransactionHistoryService $transactionHistoryService
     * @param PaymentMethodConfigRepository $methodConfigRepository
     * @param ConnectionService $connectionService
     * @param PartialPaymentService $partialPaymentService
     */
    public function __construct(
        PaymentsProxy                 $paymentsProxy,
        PaymentRequestFactory         $paymentRequestFactory,
        DonationsDataRepository       $donationsDataRepository,
        TransactionHistoryService     $transactionHistoryService,
        PaymentMethodConfigRepository $methodConfigRepository,
        ConnectionService             $connectionService,
        PartialPaymentService         $partialPaymentService
    )
    {
        $this->paymentsProxy = $paymentsProxy;
        $this->paymentRequestFactory = $paymentRequestFactory;
        $this->donationsDataRepository = $donationsDataRepository;
        $this->transactionHistoryService = $transactionHistoryService;
        $this->methodConfigRepository = $methodConfigRepository;
        $this->connectionService = $connectionService;
        $this->partialPaymentsService = $partialPaymentService;
    }

    /**
     * @throws Exception
     */
    public function startTransaction(StartTransactionRequestContext $context, Order $order = null): StartTransactionResult
    {
        $request = $this->paymentRequestFactory->crate($context);

        if ($order) {
            $paymentOrder = new \Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Order($order->getOrderData(), $order->getPspReference());
            $request->setOrder($paymentOrder);
        }

        $result = $this->paymentsProxy->startPaymentTransaction($request);

        if ($result->getResultCode()->isSuccessful()) {
            $captureType = null;
            if (!PaymentMethodCode::parse($context->getPaymentMethodCode())->isCaptureSupported()) {
                $captureType = CaptureType::immediate();
            }

            $authorizationType = null;
            $configuredPaymentMethod = $this->methodConfigRepository->getPaymentMethodByCode(
                (string)$context->getPaymentMethodCode()
            );

            if ($configuredPaymentMethod) {
                $authorizationType = $configuredPaymentMethod->getAuthorizationType();
            }

            if ($configuredPaymentMethod &&
                $configuredPaymentMethod->getAuthorizationType() &&
                $configuredPaymentMethod->getAuthorizationType()->equal(AuthorizationType::preAuthorization())) {
                $captureType = CaptureType::manual();
            }

            $connectionSettings = $this->connectionService->getConnectionData();

            $historyItem = new HistoryItem(
                $order ? $order->getPspReference() : $context->getReference(),
                $context->getReference(),
                'PAYMENT_REQUESTED',
                '',
                (new \DateTime())->format('Y-m-d H:i:s'),
                true,
                $context->getAmount(),
                $context->getPaymentMethodCode(),
                0,
                $connectionSettings && $connectionSettings->getMode() === Mode::MODE_LIVE,
                $result->getPspReference() ?? ''
            );

            $this->transactionHistoryService->createTransactionHistory(
                $context->getReference(),
                $context->getAmount()->getCurrency(),
                $captureType,
                $authorizationType,
                $historyItem,
                $order
            );
        }

        if (!$result->getResultCode()->isSuccessful()) {
            $this->removeTransactionHistoryItems($context->getReference());
        }

        if ($result->getDonationToken() &&
            in_array($request->getPaymentMethod()['type'], self::METHODS_WITH_DONATIONS, true)) {
            $donationsData = new DonationsData(
                $context->getReference(),
                $result->getDonationToken(),
                $result->getPspReference(),
                $request->getPaymentMethod()['type']
            );

            $this->donationsDataRepository->saveDonationsData($donationsData);
        }

        return $result;
    }

    /**
     * Creates order and partial transactions on Adyen.
     *
     * @param StartPartialTransactionsRequest $partialTransactionRequest
     *
     * @return StartTransactionResponse
     * @throws InvalidCurrencyCode
     * @throws InvalidPaymentMethodCodeException
     * @throws Exception
     */
    public function startPartialTransactions(StartPartialTransactionsRequest $partialTransactionRequest): StartTransactionResponse
    {
        $deductedAmount = 0;
        $giftCardsTransactionRequests = $this
            ->createGiftCardsTransactionRequests($partialTransactionRequest, $deductedAmount);

        $requests = $giftCardsTransactionRequests['requests'];
        $deductedAmount = $giftCardsTransactionRequests['deductedAmount'];

        $requests[] = $this
            ->createPaymentMethodTransactionRequest($partialTransactionRequest, $deductedAmount);
        
        $amount = Amount::fromFloat(
            $partialTransactionRequest->getOrderTotalAmount(),
            Currency::fromIsoCode($partialTransactionRequest->getCurrency())
        );

        $orderCreateResult = $this->partialPaymentsService
            ->createOrder($partialTransactionRequest->getReference(), $amount);
        $order = new Order($orderCreateResult->getOrderData(), $orderCreateResult->getPspReference());

        return $this->startTransactions($requests, $order);
    }

    /**
     * @throws Exception
     */
    public function updatePaymentDetails(UpdatePaymentDetailsRequest $request): UpdatePaymentDetailsResult
    {
        $result = $this->paymentsProxy->updatePaymentDetails($request);

        if ($result->getDonationToken() && $result->getMerchantReference() &&
            in_array($result->getPaymentMethod(), self::METHODS_WITH_DONATIONS, true)) {
            $donationsData = new DonationsData(
                $result->getMerchantReference(),
                $result->getDonationToken(),
                $result->getPspReference(),
                $result->getPaymentMethod()
            );

            $this->donationsDataRepository->saveDonationsData($donationsData);
        }

        if (!$result->getResultCode()->isSuccessful()) {
            $this->removeTransactionHistoryItems($result->getMerchantReference());
        }

        return $result;
    }

    /**
     * Updates order amount for paypal transaction.
     *
     * @param PayPalUpdateOrderRequest $request
     *
     * @return PayPalUpdateOrderResponse
     *
     * @throws Exception
     */
    public function paypalUpdateOrder(PayPalUpdateOrderRequest $request): PayPalUpdateOrderResponse
    {
        return $this->paymentsProxy->paypalUpdateOrder($request);
    }

    /**
     * @param string $merchantReference
     * @return void
     *
     * @throws InvalidMerchantReferenceException
     */
    public function removeTransactionHistoryItems(string $merchantReference): void
    {
        $transactionHistory = $this->transactionHistoryService->getTransactionHistory($merchantReference);
        $historyItems = [];

        foreach ($transactionHistory->collection()->getAll() as $transactionHistoryItem) {
            if ($transactionHistoryItem->getEventCode() === 'PAYMENT_REQUESTED') {
                continue;
            }

            $historyItems[] = $transactionHistoryItem;
        }

        $newHistory = new TransactionHistory(
            $merchantReference,
            $transactionHistory->getCaptureType(),
            $transactionHistory->getCaptureDelay(),
            $transactionHistory->getCurrency(),
            $transactionHistory->getAuthorizationType(),
            $historyItems,
            $transactionHistory->getOrderData() ?: '',
            $transactionHistory->getOrderPspReference() ?: '',
            $transactionHistory->getAuthorizationPspReferences()
        );

        $this->transactionHistoryService->setTransactionHistory($newHistory);

        if ($transactionHistory->getOrderData()) {
            $this->partialPaymentsService->cancelOrder($transactionHistory->getOrderPspReference(), $transactionHistory->getOrderData());
        }
    }

    /**
     * Creates partial transaction requests for gift cards.
     *
     * @param StartPartialTransactionsRequest $partialTransactionRequest
     * @param float $deductedAmount
     * @return array
     * @throws InvalidCurrencyCode
     */
    private function createGiftCardsTransactionRequests(
        StartPartialTransactionsRequest $partialTransactionRequest,
        float                           $deductedAmount
    ): array
    {
        $requests = [];
        foreach ($partialTransactionRequest->getGiftCardsStateData() as $giftCardData) {
            $giftCardAmount = Amount::fromInt(
                $giftCardData['cardAmount'],
                Currency::fromIsoCode($partialTransactionRequest->getCurrency())
            );
            $requests[] = new StartTransactionRequest(
                $giftCardData['paymentMethod']['brand'],
                $giftCardAmount,
                $partialTransactionRequest->getReference(),
                $partialTransactionRequest->getReturnUrl(),
                $giftCardData,
                [],
                $partialTransactionRequest->getShopperReference()
            );

            $deductedAmount += $giftCardAmount->getPriceInCurrencyUnits();
        }

        return [
            'requests' => $requests,
            'deductedAmount' => $deductedAmount
        ];
    }

    /**
     * Creates partial transaction request for payment method.
     *
     * @param StartPartialTransactionsRequest $partialTransactionRequest
     * @param float $deductedAmount
     * @return StartTransactionRequest
     * @throws InvalidCurrencyCode
     */
    private function createPaymentMethodTransactionRequest(
        StartPartialTransactionsRequest $partialTransactionRequest,
        float                           $deductedAmount
    ): StartTransactionRequest
    {
        return new StartTransactionRequest(
            $partialTransactionRequest->getPaymentMethodType(),
            Amount::fromFloat(
                $partialTransactionRequest->getOrderTotalAmount() - $deductedAmount,
                Currency::fromIsoCode($partialTransactionRequest->getCurrency())
            ),
            $partialTransactionRequest->getReference(),
            $partialTransactionRequest->getReturnUrl(),
            $partialTransactionRequest->getPaymentMethodStateData(),
            [],
            $partialTransactionRequest->getShopperReference()
        );
    }

    /**
     * Creates transactions on Adyen for every transaction request.
     *
     * @param array $requests
     * @param Order $order
     * @return StartTransactionResponse
     * @throws InvalidPaymentMethodCodeException
     * @throws Exception
     */
    private function startTransactions(array $requests, Order $order): StartTransactionResponse
    {
        $results = [];

        foreach ($requests as $request) {
            $startTransactionRequest = new StartTransactionRequestContext(
                PaymentMethodCode::parse($request->getPaymentMethodType()),
                $request->getAmount(),
                $request->getReference(),
                $request->getReturnUrl(),
                new DataBag($request->getStateData()),
                new DataBag($request->getSessionData()),
                $request->getShopperReference()
            );

            $response = new StartTransactionResponse(
                $this->startTransaction(
                    $startTransactionRequest, $order
                )
            );

            $results[] = $response;

            if (!$response->isSuccessful()) {
                break;
            }
        }

        return end($results);
    }
}
