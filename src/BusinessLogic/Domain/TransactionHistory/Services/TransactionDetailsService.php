<?php

namespace Adyen\Core\BusinessLogic\Domain\TransactionHistory\Services;

use Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Validator\AuthorizationAdjustmentValidator;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\CurrencyMismatchException;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\InvalidPaymentMethodCodeException;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Currency;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PaymentMethodCode;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionSettings;
use Adyen\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Models\CaptureType;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Services\GeneralSettingsService;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\PaymentMethod;
use Adyen\Core\BusinessLogic\Domain\Payment\Services\PaymentService;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Exceptions\InvalidMerchantReferenceException;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models\HistoryItem;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models\HistoryItemCollection;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory;
use Adyen\Core\BusinessLogic\Domain\Integration\Order\OrderService;
use Adyen\Core\Infrastructure\Utility\TimeProvider;
use Adyen\Webhook\EventCodes;
use Exception;

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
     * @var GeneralSettingsService
     */
    private $generalSettingsService;

    /**
     * @var OrderService
     */
    private $orderService;

    /**
     * @param ConnectionService $connectionService
     * @param TransactionHistoryService $historyService
     * @param GeneralSettingsService $generalSettingsService
     * @param OrderService $orderService
     */
    public function __construct(
        ConnectionService         $connectionService,
        TransactionHistoryService $historyService,
        GeneralSettingsService    $generalSettingsService,
        OrderService              $orderService
    )
    {
        $this->connectionService = $connectionService;
        $this->historyService = $historyService;
        $this->generalSettingsService = $generalSettingsService;
        $this->orderService = $orderService;
    }

    /**
     * @param string $merchantReference
     * @param string $storeId
     *
     * @return array
     *
     * @throws InvalidMerchantReferenceException
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
        $isMerchantConnected = $connectionSettings
            && $connectionSettings->getActiveConnectionData()
            && $connectionSettings->getActiveConnectionData()->getMerchantId();

        $orderOpen = $transactionHistory->collection()->filterAllByEventCode(EventCodes::ORDER_OPENED)->getAll();
        $authorization = $transactionHistory->collection()->filterAllByEventCode(EventCodes::AUTHORISATION)->getAll();
        $orderClosed = $transactionHistory->collection()->filterAllByEventCode(EventCodes::ORDER_CLOSED)->getAll();
        $transactions = array_merge($orderOpen, $authorization, $orderClosed);

        usort($transactions, static function ($a, $b) {
            return strtotime($a->getDateAndTime()) - strtotime($b->getDateAndTime());
        });

        foreach ($transactions as $item) {
            if (!empty($transactionHistory->getAuthorizationPspReferences()) &&
                $item->getEventCode() === EventCodes::AUTHORISATION &&
                !in_array($item->getAuthorizationPspReference(), $transactionHistory->getAuthorizationPspReferences(), true)) {
                continue;
            }

            try {
                if (in_array($item->getEventCode(), [EventCodes::ORDER_OPENED, EventCodes::ORDER_CLOSED], true)) {
                    $result[] = [
                        'pspReference' => $item->getPspReference(),
                        'date' => $item->getDateAndTime(),
                        'status' => $item->getStatus(),
                        'paymentMethod' => !empty($item->getPaymentMethod()) ? $this->getLogo($item->getPaymentMethod()) : '',
                        'eventCode' => $item->getEventCode(),
                        'success' => true,
                        'merchantAccountCode' => $connectionSettings ?
                            $connectionSettings->getActiveConnectionData()->getMerchantId() : '',
                        'merchantReference' => $item->getMerchantReference(),
                        'storeId' => $storeId,
                    ];
                }

                if ($item->getEventCode() === EventCodes::AUTHORISATION) {
                    $result[] = $this->formatItem($item, $transactionHistory, $isMerchantConnected, $connectionSettings, $storeId);
                }
            } catch (CurrencyMismatchException $e) {
                return [];
            }
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
     */
    private function isSeparateCaptureSupported(
        string                $code,
        HistoryItemCollection $collection,
        Amount                $captureAmount,
        Amount                $authorizedAmount
    ): bool
    {
        try {
            return !empty($code) && $this->parseCode($code)->isCaptureSupported()
                && !$collection
                    ->filterByEventCode(EventCodes::CANCELLATION)
                    ->filterByStatus(true)
                    ->isEmpty()
                && $captureAmount->getPriceInCurrencyUnits() < $authorizedAmount->getPriceInCurrencyUnits();
        } catch (InvalidPaymentMethodCodeException $exception) {
            return false;
        }
    }

    /**
     * @param string $code
     * @param TransactionHistory $transactionHistory
     * @param Amount $captureAmount
     * @param Amount $authorizedAmount
     *
     * @return bool
     */
    private function isPartialCaptureSupported(
        string                $code,
        HistoryItemCollection $collection,
        Amount                $captureAmount,
        Amount                $authorizedAmount
    ): bool
    {
        try {
            return !empty($code) && $this->parseCode($code)->isPartialCaptureSupported()
                && !$collection
                    ->filterByEventCode(EventCodes::CANCELLATION)
                    ->filterByStatus(true)
                    ->isEmpty()
                && $captureAmount->getPriceInCurrencyUnits() < $authorizedAmount->getPriceInCurrencyUnits();
        } catch (InvalidPaymentMethodCodeException $exception) {
            return false;
        }
    }

    /**
     * @param string $code
     * @param Amount $refundAmount
     * @param Amount $captureAmount
     * @param Amount $cancellationAmount
     *
     * @return bool
     */
    private function isPartialRefundSupported(
        string $code,
        Amount $refundAmount,
        Amount $captureAmount,
        Amount $cancellationAmount
    ): bool
    {
        try {
            return !empty($code) &&
                $this->parseCode($code)->isPartialRefundSupported() &&
                $refundAmount->getPriceInCurrencyUnits() < $captureAmount->getPriceInCurrencyUnits() &&
                $cancellationAmount->getPriceInCurrencyUnits() !== $refundAmount->getPriceInCurrencyUnits() +
                $captureAmount->getPriceInCurrencyUnits();
        } catch (InvalidPaymentMethodCodeException $exception) {
            return false;
        }
    }

    /**
     * @param string $code
     * @param Amount $refundAmount
     * @param Amount $captureAmount
     * @param Amount $cancellationAmount
     *
     * @return bool
     */
    private function isRefundSupported(
        string $code,
        Amount $refundAmount,
        Amount $captureAmount,
        Amount $cancellationAmount
    ): bool
    {
        try {
            return !empty($code) &&
                $this->parseCode($code)->isRefundSupported() &&
                $refundAmount->getPriceInCurrencyUnits() < $captureAmount->getPriceInCurrencyUnits() &&
                $cancellationAmount->getPriceInCurrencyUnits() !== $refundAmount->getPriceInCurrencyUnits() +
                $captureAmount->getPriceInCurrencyUnits();
        } catch (InvalidPaymentMethodCodeException $exception) {
            return false;
        }
    }

    /**
     * @param Amount $captureAmount
     * @param Amount $authorizedAmount
     * @param Amount $cancelledAmount
     *
     * @return bool
     */
    private function isCancellationSupported(
        Amount $captureAmount,
        Amount $authorizedAmount,
        Amount $cancelledAmount
    ): bool
    {
        return !$cancelledAmount->getPriceInCurrencyUnits() &&
            $captureAmount->getPriceInCurrencyUnits() < $authorizedAmount->getPriceInCurrencyUnits();
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

    /**
     * @param TransactionHistory $transactionHistory
     *
     * @return bool
     *
     * @throws CurrencyMismatchException
     */
    private function shouldDisplayPaymentLink(TransactionHistory $transactionHistory): bool
    {
        $generalSettings = $this->generalSettingsService->getGeneralSettings();

        if (!$generalSettings || !$generalSettings->isEnablePayByLink()) {
            return false;
        }

        if (!$this->orderService->getOrderAmount($transactionHistory->getMerchantReference())->getPriceInCurrencyUnits()) {
            return false;
        }

        if ($transactionHistory->collection()->isEmpty()) {
            return true;
        }

        $failed = true;
        $cancelled = true;
        $allPaymentsCancelled = true;

        foreach ($transactionHistory->getAuthorizationPspReferences() as $pspReference) {
            $items = $transactionHistory->collection()->filterByOriginalReference($pspReference);
            $item = $items->last();

            $cancelled &= $item->getPaymentState() === 'cancelled';
            $failed &= $item->getPaymentState() === 'failed';

            $allPaymentsCancelled &= $items->filterByEventCode(EventCodes::CANCELLATION)
                    ->getAmount($transactionHistory->getCurrency())->getPriceInCurrencyUnits() ===
                $items->filterByEventCode(EventCodes::AUTHORISATION)
                    ->getAmount($transactionHistory->getCurrency())->getPriceInCurrencyUnits();
        }

        return $cancelled ||
            $failed ||
            $allPaymentsCancelled ||
            empty($transactionHistory->getAuthorizationPspReferences());
    }

    /**
     * @param TransactionHistory $transactionHistory
     *
     * @return bool
     */
    private function isAuthorizationAdjustmentAvailable(TransactionHistory $transactionHistory): bool
    {
        try {
            AuthorizationAdjustmentValidator::validateAdjustmentPossibility($transactionHistory);

            return true;
        } catch (Exception $exception) {
            return false;
        }
    }

    /**
     * @param TransactionHistory $transactionHistory
     *
     * @return string|null
     */
    private function getAuthorizationAdjustmentDate(TransactionHistory $transactionHistory): ?string
    {
        $authorizationAdjustmentItems = $transactionHistory->collection()->filterByEventCode('AUTHORISATION_ADJUSTMENT');

        if ($authorizationAdjustmentItems->isEmpty()) {
            return null;
        }

        return $authorizationAdjustmentItems->last()->getDateAndTime();
    }

    /**
     * @param TransactionHistory $history
     * @param HistoryItemCollection $collection
     *
     * @return Amount
     *
     * @throws CurrencyMismatchException
     */
    public function getCapturedAmount(TransactionHistory $history, HistoryItemCollection $collection): Amount
    {
        $authorisedItem = $collection->filterByEventCode(EventCodes::AUTHORISATION)->first();

        if (!$authorisedItem) {
            return Amount::fromInt(0, Currency::getDefault());
        }

        if ($authorisedItem->getCaptureType() && $authorisedItem->getCaptureType()->equal(CaptureType::manual())) {
            return $collection->filterByEventCode(EventCodes::CAPTURE)->getAmount($history->getCurrency());
        }

        if ($authorisedItem->getCaptureType() && ($authorisedItem->getCaptureType()->equal(CaptureType::immediate())
                || $authorisedItem->getCaptureType()->equal(CaptureType::unknown()))) {
            return $collection->filterByEventCode(EventCodes::AUTHORISATION)->getAmount($history->getCurrency());
        }

        $authorisedDate = TimeProvider::getInstance()->deserializeDateString(
            $authorisedItem->getDateAndTime()
        )->getTimestamp();

        if ($authorisedDate + $history->getCaptureDelay() * 3600 <
            TimeProvider::getInstance()->getCurrentLocalTime()->getTimestamp()) {
            return $collection->filterByEventCode(EventCodes::AUTHORISATION)->getAmount($history->getCurrency());
        }

        return $collection->filterByEventCode(EventCodes::CAPTURE)->getAmount($history->getCurrency());
    }

    public function getCapturableAmount(
        TransactionHistory    $history,
        HistoryItemCollection $collection,
        Amount                $capturedAmount
    ): Amount
    {
        $authorisationAdjustmentItem = $collection->filterByEventCode('AUTHORISATION_ADJUSTMENT')
            ->filterByStatus(true)->last();
        $authorisationAmount = $collection->filterByEventCode('AUTHORISATION')
            ->filterByStatus(true)->getTotalAmount($history->getCurrency());

        if (!$authorisationAdjustmentItem) {
            return $authorisationAmount->minus($capturedAmount);
        }

        $capturedAfterAdjustmentElements = $collection->trimFromHistoryItem($authorisationAdjustmentItem);
        $capturedAfterAdjustmentAmount = $capturedAfterAdjustmentElements->filterByEventCode('CAPTURE')
            ->filterByStatus(true)->getTotalAmount($history->getCurrency());

        return $authorisationAdjustmentItem->getAmount()->minus($capturedAfterAdjustmentAmount);
    }

    /**
     * @param HistoryItem $item
     * @param TransactionHistory $transactionHistory
     * @param bool $isMerchantConnected
     * @param ConnectionSettings|null $connectionSettings
     * @param string $storeId
     *
     * @return array
     *
     * @throws CurrencyMismatchException
     */
    public function formatItem(
        HistoryItem $item,
        TransactionHistory $transactionHistory,
        bool $isMerchantConnected,
        ?ConnectionSettings $connectionSettings,
        string $storeId
    ): array
    {
        $paymentMethod = $item->getPaymentMethod();
        $isCaptureTypeKnown = $item->getCaptureType() && $item->getCaptureType()->equal(CaptureType::unknown());
        $authorizationAmount = $item->getAmount();
        $samePaymentItems = $transactionHistory->collection()->filterByOriginalReference($item->getPspReference());

        if ($samePaymentItems->isEmpty()) {
            $samePaymentItems = $transactionHistory->collection();
        }

        $refundAmount = $samePaymentItems->filterByEventCode(EventCodes::REFUND)->getAmount($transactionHistory->getCurrency());
        $captureAmount = $this->getCapturedAmount($transactionHistory, $samePaymentItems);
        $capturableAmount = $this->getCapturableAmount($transactionHistory, $samePaymentItems, $captureAmount);
        $cancelledAmount = $samePaymentItems->filterByEventCode(EventCodes::CANCELLATION)->getAmount($transactionHistory->getCurrency());
        $authorizationAdjustmentAmount = $samePaymentItems->filterByEventCode(EventCodes::AUTHORISATION)
            ->getAmount($transactionHistory->getCurrency());
        $cancel = $isMerchantConnected && $this->isCancellationSupported(
                $captureAmount,
                $authorizationAmount,
                $cancelledAmount
            );
        $url = $transactionHistory->getAdyenPaymentLinkFor($item->getPspReference());
        $separateCapture = $isMerchantConnected && !empty($paymentMethod) && $this->isSeparateCaptureSupported(
                $paymentMethod,
                $samePaymentItems,
                $captureAmount,
                $authorizationAmount
            );
        $partialCapture = $isMerchantConnected && !empty($paymentMethod) &&
            $this->isPartialCaptureSupported($paymentMethod, $samePaymentItems, $captureAmount, $authorizationAmount);
        $refund = $isMerchantConnected && $this->isRefundSupported($paymentMethod, $refundAmount, $captureAmount,
                $cancelledAmount);
        $partialRefund = $isMerchantConnected && !empty($paymentMethod) && $this->isPartialRefundSupported(
                $paymentMethod,
                $refundAmount,
                $captureAmount,
                $cancelledAmount
            );
        $result = [];

        foreach ($samePaymentItems->getAll() as $samePaymentItem) {
            if (in_array($samePaymentItem->getEventCode(), ['PAYMENT_REQUESTED', EventCodes::ORDER_OPENED, EventCodes::ORDER_CLOSED], true)) {
                continue;
            }

            $result[] = [
                'pspReference' => $samePaymentItem->getPspReference(),
                'date' => $samePaymentItem->getDateAndTime(),
                'status' => $samePaymentItem->getStatus(),
                'paymentMethod' => !empty($samePaymentItem->getPaymentMethod()) ? $this->getLogo($samePaymentItem->getPaymentMethod()) : '',
                'eventCode' => $samePaymentItem->getEventCode(),
                'success' => true,
                'merchantAccountCode' => $connectionSettings ?
                    $connectionSettings->getActiveConnectionData()->getMerchantId() : '',
                'paidAmount' => $authorizationAmount ? $authorizationAmount->getPriceInCurrencyUnits() : '',
                'amountCurrency' => $authorizationAmount ? $authorizationAmount->getCurrency()->getIsoCode() : '',
                'refundedAmount' => $refundAmount ? $refundAmount->getPriceInCurrencyUnits() : '',
                'viewOnAdyenUrl' => $url,
                'merchantReference' => $samePaymentItem->getMerchantReference(),
                'storeId' => $storeId,
                'currencyIso' => $authorizationAmount->getCurrency()->getIsoCode(),
                'captureSupported' => $isCaptureTypeKnown ? $separateCapture : true,
                'captureAmount' => $captureAmount->getPriceInCurrencyUnits(),
                'partialCapture' => $isCaptureTypeKnown ? $partialCapture : true,
                'refund' => $isCaptureTypeKnown ? $refund : true,
                'partialRefund' => $isCaptureTypeKnown ? $partialRefund : true,
                'refundAmount' => $refundAmount->getPriceInCurrencyUnits(),
                'refundableAmount' => $isCaptureTypeKnown ?
                    $captureAmount->getPriceInCurrencyUnits() - $refundAmount->getPriceInCurrencyUnits() :
                    $authorizationAmount->getPriceInCurrencyUnits(),
                'capturableAmount' => $capturableAmount->getPriceInCurrencyUnits(),
                'riskScore' => $transactionHistory->getRiskScore(),
                'cancelSupported' => $isCaptureTypeKnown ? $cancel : true,
                'paymentMethodType' => $samePaymentItem->getPaymentMethod(),
                'paymentState' => $samePaymentItem->getPaymentState(),
                'displayPaymentLink' => $this->shouldDisplayPaymentLink($transactionHistory),
                'paymentLink' => $transactionHistory->getPaymentLink() ? $transactionHistory->getPaymentLink()->getUrl() : '',
                'authorizationAdjustmentAvailable' => $this->isAuthorizationAdjustmentAvailable($transactionHistory),
                'authorizationAdjustmentDate' => $this->getAuthorizationAdjustmentDate($transactionHistory),
                'authorizationAdjustmentAmount' => $authorizationAdjustmentAmount ? $authorizationAdjustmentAmount->getPriceInCurrencyUnits() : '',
            ];
        }

        return $result;
    }
}
