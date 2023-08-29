<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Services;

use Adyen\Core\BusinessLogic\Domain\Checkout\AdyenGiving\Models\DonationsData;
use Adyen\Core\BusinessLogic\Domain\Checkout\AdyenGiving\Repositories\DonationsDataRepository;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Factory\PaymentRequestFactory;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PaymentMethodCode;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\StartTransactionRequestContext;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\StartTransactionResult;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\UpdatePaymentDetailsRequest;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\UpdatePaymentDetailsResult;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Proxies\PaymentsProxy;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Models\CaptureType;
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
     * @param PaymentsProxy $paymentsProxy
     * @param PaymentRequestFactory $paymentRequestFactory
     * @param DonationsDataRepository $donationsDataRepository
     * @param TransactionHistoryService $transactionHistoryService
     */
    public function __construct(
        PaymentsProxy $paymentsProxy,
        PaymentRequestFactory $paymentRequestFactory,
        DonationsDataRepository $donationsDataRepository,
        TransactionHistoryService $transactionHistoryService
    ) {
        $this->paymentsProxy = $paymentsProxy;
        $this->paymentRequestFactory = $paymentRequestFactory;
        $this->donationsDataRepository = $donationsDataRepository;
        $this->transactionHistoryService = $transactionHistoryService;
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

            $this->transactionHistoryService->createTransactionHistory(
                $context->getReference(),
                $context->getAmount()->getCurrency(),
                $captureType
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
}
