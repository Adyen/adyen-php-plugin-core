<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Services;

use Adyen\Core\BusinessLogic\Domain\Checkout\AdyenGiving\Models\DonationsData;
use Adyen\Core\BusinessLogic\Domain\Checkout\AdyenGiving\Repositories\DonationsDataRepository;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Factory\PaymentRequestFactory;
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
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models\HistoryItemCollection;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Services\TransactionHistoryService;
use Exception;

/**
 * Class PaymentService
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
        PartialPaymentService $partialPaymentService
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
}
