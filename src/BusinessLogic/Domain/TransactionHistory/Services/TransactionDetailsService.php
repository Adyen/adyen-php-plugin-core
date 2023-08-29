<?php

namespace Adyen\Core\BusinessLogic\Domain\TransactionHistory\Services;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\CurrencyMismatchException;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\InvalidPaymentMethodCodeException;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PaymentMethodCode;
use Adyen\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Models\CaptureType;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\PaymentMethod;
use Adyen\Core\BusinessLogic\Domain\Payment\Services\PaymentService;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Exceptions\InvalidMerchantReferenceException;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory;

/**
 * Class TransactionDetailsService
 *
 * @package Adyen\Core\BusinessLogic\Domain\TransactionHistory\Services
 */
class TransactionDetailsService
{
    /**
     * @var ConnectionService
     */
    private $connectionService;
    /**
     * @var TransactionHistoryService
     */
    private $historyService;

    /**
     * @param ConnectionService $connectionService
     * @param TransactionHistoryService $historyService
     */
    public function __construct(
        ConnectionService $connectionService,
        TransactionHistoryService $historyService
    ) {
        $this->connectionService = $connectionService;
        $this->historyService = $historyService;
    }

    /**
     * @param string $merchantReference
     * @param string $storeId
     *
     * @return array
     *
     * @throws InvalidMerchantReferenceException
     * @throws InvalidPaymentMethodCodeException
     * @throws CurrencyMismatchException
     */
    public function getTransactionDetails(string $merchantReference, string $storeId): array
    {
        $transactionHistory = $this->historyService->getTransactionHistory($merchantReference);
        if ($transactionHistory->collection()->isEmpty()) {
            return [];
        }

        $connectionSettings = $this->connectionService->getConnectionData();
        $result = [];
        $paymentMethod = $transactionHistory->collection()->first()->getPaymentMethod();
        $isCaptureTypeKnown = !$transactionHistory->getCaptureType()->equal(CaptureType::unknown());
        $isMerchantConnected = $connectionSettings
            && $connectionSettings->getActiveConnectionData()
            && $connectionSettings->getActiveConnectionData()->getMerchantId();
        try {
            $authorizationAmount = $transactionHistory->getTotalAmountForEventCode('AUTHORISATION');
            $refundAmount = $transactionHistory->getTotalAmountForEventCode('REFUND');
            $captureAmount = $transactionHistory->getCapturedAmount();
            $capturableAmount = $isCaptureTypeKnown ? $authorizationAmount->minus(
                $captureAmount
            )->getPriceInCurrencyUnits() : $authorizationAmount->getPriceInCurrencyUnits();
            $cancelledAmount = $transactionHistory->getTotalAmountForEventCode('CANCELLATION');
            $cancel = $isMerchantConnected && $this->isCancellationSupported(
                    $captureAmount,
                    $authorizationAmount,
                    $cancelledAmount
                );
        } catch (CurrencyMismatchException $e) {
            return [];
        }

        $url = $transactionHistory->getAdyenPaymentLinkFor(
            $transactionHistory->collection()->first()->getPspReference()
        );
        $separateCapture = $isMerchantConnected && $this->isSeparateCaptureSupported(
                $paymentMethod,
                $transactionHistory,
                $captureAmount,
                $authorizationAmount
            );
        $partialCapture = $isMerchantConnected &&
            $this->isPartialCaptureSupported($paymentMethod, $transactionHistory, $captureAmount, $authorizationAmount);
        $refund = $isMerchantConnected && $this->isRefundSupported($paymentMethod, $refundAmount, $captureAmount);
        $partialRefund = $isMerchantConnected && $this->isPartialRefundSupported(
                $paymentMethod,
                $refundAmount,
                $captureAmount
            );

        foreach ($transactionHistory->collection()->getAll() as $item) {
            $result[] = [
                'pspReference' => $item->getPspReference(),
                'date' => $item->getDateAndTime(),
                'status' => $item->getStatus(),
                'paymentMethod' => $this->getLogo($item->getPaymentMethod()),
                'eventCode' => $item->getEventCode(),
                'success' => true,
                'merchantAccountCode' => $connectionSettings ?
                    $connectionSettings->getActiveConnectionData()->getMerchantId() : '',
                'paidAmount' => $authorizationAmount ? $authorizationAmount->getPriceInCurrencyUnits() : '',
                'amountCurrency' => $authorizationAmount ? $authorizationAmount->getCurrency()->getIsoCode() : '',
                'refundedAmount' => $refundAmount ? $refundAmount->getPriceInCurrencyUnits() : '',
                'viewOnAdyenUrl' => $url,
                'merchantReference' => $item->getMerchantReference(),
                'storeId' => $storeId,
                'currencyIso' => $authorizationAmount->getCurrency()->getIsoCode(),
                'captureSupported' => $isCaptureTypeKnown ? $separateCapture : true,
                'captureAmount' => $captureAmount->getPriceInCurrencyUnits(),
                'partialCapture' => $isCaptureTypeKnown ? $partialCapture : true,
                'refund' => $isCaptureTypeKnown ? $refund : true,
                'partialRefund' => $isCaptureTypeKnown ? $partialRefund : true,
                'refundAmount' => $refundAmount->getPriceInCurrencyUnits(),
                'refundableAmount' => $isCaptureTypeKnown ? $captureAmount->getPriceInCurrencyUnits(
                    ) - $refundAmount->getPriceInCurrencyUnits() : $authorizationAmount->getPriceInCurrencyUnits(),
                'capturableAmount' => $capturableAmount,
                'riskScore' => $transactionHistory->getRiskScore(),
                'cancelSupported' => $isCaptureTypeKnown ? $cancel : true,
                'paymentMethodType' => $item->getPaymentMethod(),
                'paymentState' => $item->getPaymentState()
            ];
        }

        return $result;
    }

    /**
     * @param string $code
     *
     * @return string
     */
    private function getLogo(string $code): string
    {
        if (in_array($code, PaymentService::CREDIT_CARD_BRANDS, true)) {
            $code = PaymentService::CREDIT_CARD_CODE;
        }

        return PaymentMethod::getLogoUrl($code);
    }

    /**
     * @param string $code
     * @param TransactionHistory $transactionHistory
     * @param Amount $captureAmount
     * @param Amount $authorizedAmount
     *
     * @return bool
     *
     * @throws InvalidPaymentMethodCodeException
     */
    private function isSeparateCaptureSupported(
        string $code,
        TransactionHistory $transactionHistory,
        Amount $captureAmount,
        Amount $authorizedAmount
    ): bool {
        return $this->parseCode($code)->isCaptureSupported()
            && !$this->getStatusByEventCode(
                $transactionHistory,
                'CANCELLATION'
            ) && $captureAmount->getPriceInCurrencyUnits() < $authorizedAmount->getPriceInCurrencyUnits();
    }

    /**
     * @param string $code
     * @param TransactionHistory $transactionHistory
     * @param Amount $captureAmount
     * @param Amount $authorizedAmount
     *
     * @return bool
     *
     * @throws InvalidPaymentMethodCodeException
     */
    private function isPartialCaptureSupported(
        string $code,
        TransactionHistory $transactionHistory,
        Amount $captureAmount,
        Amount $authorizedAmount
    ): bool {
        return $this->parseCode($code)->isPartialCaptureSupported()
            && !$this->getStatusByEventCode($transactionHistory, 'CANCELLATION')
            && $captureAmount->getPriceInCurrencyUnits() < $authorizedAmount->getPriceInCurrencyUnits();
    }

    /**
     * @param string $code
     * @param Amount $refundAmount
     * @param Amount $captureAmount
     *
     * @return bool
     *
     * @throws InvalidPaymentMethodCodeException
     */
    private function isPartialRefundSupported(
        string $code,
        Amount $refundAmount,
        Amount $captureAmount
    ): bool {
        $result = $this->parseCode($code)->isPartialRefundSupported();

        return $result && $refundAmount->getPriceInCurrencyUnits()
            < $captureAmount->getPriceInCurrencyUnits();
    }

    /**
     * @param string $code
     * @param Amount $refundAmount
     * @param Amount $captureAmount
     * @return bool
     *
     * @throws InvalidPaymentMethodCodeException
     */
    private function isRefundSupported(
        string $code,
        Amount $refundAmount,
        Amount $captureAmount
    ): bool {
        $result = $this->parseCode($code)->isRefundSupported();

        return $result && $refundAmount->getPriceInCurrencyUnits()
            < $captureAmount->getPriceInCurrencyUnits();
    }

    /**
     * @param Amount $captureAmount
     * @param Amount $authorizedAmount
     * @param Amount $cancelledAmount
     *
     * @return bool
     *
     * @throws CurrencyMismatchException
     */
    private function isCancellationSupported(
        Amount $captureAmount,
        Amount $authorizedAmount,
        Amount $cancelledAmount
    ): bool {
        return $captureAmount->getPriceInCurrencyUnits() < $authorizedAmount->minus(
                $cancelledAmount
            )->getPriceInCurrencyUnits();
    }

    /**
     * @param TransactionHistory $transactionHistory
     * @param string $eventCode
     *
     * @return bool
     */
    private function getStatusByEventCode(TransactionHistory $transactionHistory, string $eventCode): bool
    {
        return !$transactionHistory->collection()
            ->filterByEventCode($eventCode)
            ->filterByStatus(true)
            ->isEmpty();
    }

    /**
     * @param string $code
     *
     * @return PaymentMethodCode
     *
     * @throws InvalidPaymentMethodCodeException
     */
    private function parseCode(string $code): PaymentMethodCode
    {
        foreach (PaymentMethodCode::SUPPORTED_PAYMENT_METHODS as $methodCode) {
            if (strpos($code, $methodCode) !== false) {
                return PaymentMethodCode::parse($methodCode);
            }
        }

        return PaymentMethodCode::parse($code);
    }
}
