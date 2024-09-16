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
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Models\CaptureType;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\AuthorizationType;
use Adyen\Core\BusinessLogic\Domain\Payment\Repositories\PaymentMethodConfigRepository;
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
     * @param PaymentsProxy $paymentsProxy
     * @param PaymentRequestFactory $paymentRequestFactory
     * @param DonationsDataRepository $donationsDataRepository
     * @param TransactionHistoryService $transactionHistoryService
     * @param PaymentMethodConfigRepository $methodConfigRepository
     */
    public function __construct(
        PaymentsProxy $paymentsProxy,
        PaymentRequestFactory $paymentRequestFactory,
        DonationsDataRepository $donationsDataRepository,
        TransactionHistoryService $transactionHistoryService,
        PaymentMethodConfigRepository $methodConfigRepository
    ) {
        $this->paymentsProxy = $paymentsProxy;
        $this->paymentRequestFactory = $paymentRequestFactory;
        $this->donationsDataRepository = $donationsDataRepository;
        $this->transactionHistoryService = $transactionHistoryService;
        $this->methodConfigRepository = $methodConfigRepository;
    }

    /**
     * @throws Exception
     */
    public function startTransaction(StartTransactionRequestContext $context): StartTransactionResult
    {
        $request = $this->paymentRequestFactory->crate($context);
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

            if($configuredPaymentMethod){
                $authorizationType = $configuredPaymentMethod->getAuthorizationType();
            }

            if($configuredPaymentMethod &&
                $configuredPaymentMethod->getAuthorizationType() &&
                $configuredPaymentMethod->getAuthorizationType()->equal(AuthorizationType::preAuthorization())){

                $captureType = CaptureType::manual();
            }

            $this->transactionHistoryService->createTransactionHistory(
                $context->getReference(),
                $context->getAmount()->getCurrency(),
                $captureType,
                $authorizationType
            );
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
}
