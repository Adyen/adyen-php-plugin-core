<?php

namespace Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Handlers;

use Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Exceptions\AdjustmentRequestAlreadySentException;
use Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Exceptions\AmountNotChangedException;
use Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Exceptions\InvalidAmountException;
use Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Exceptions\InvalidAuthorizationTypeException;
use Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Exceptions\InvalidPaymentStateException;
use Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Exceptions\OrderFullyCapturedException;
use Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Exceptions\PaymentLinkExistsException;
use Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Models\AuthorizationAdjustmentRequest;
use Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Proxies\AuthorizationAdjustmentProxy;
use Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Validator\AuthorizationAdjustmentValidator;
use Adyen\Core\BusinessLogic\Domain\Cancel\Handlers\CancelHandler;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\CurrencyMismatchException;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\AuthorizationAdjustment\FailedAuthorizationAdjustmentRequestEvent;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\AuthorizationAdjustment\SuccessfulAuthorizationAdjustmentRequestEvent;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\ShopEvents;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Services\ShopNotificationService;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Exceptions\InvalidMerchantReferenceException;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models\HistoryItem;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Services\TransactionHistoryService;
use Adyen\Core\Infrastructure\Utility\TimeProvider;
use DateTimeInterface;
use Exception;

/**
 * Class AuthorizationAdjustmentHandler.
 *
 * @package Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Handlers
 */
class AuthorizationAdjustmentHandler
{
    /**
     * @var TransactionHistoryService
     */
    private $transactionHistoryService;

    /**
     * @var ShopNotificationService
     */
    private $shopNotificationService;

    /**
     * @var AuthorizationAdjustmentProxy
     */
    private $authorizationAdjustmentProxy;

    /**
     * @var ConnectionService
     */
    private $connectionService;

    /**
     * @var CancelHandler
     */
    private $cancelHandler;

    /**
     * @param TransactionHistoryService $transactionHistoryService
     * @param ShopNotificationService $shopNotificationService
     * @param AuthorizationAdjustmentProxy $authorizationAdjustmentProxy
     * @param ConnectionService $connectionService
     * @param CancelHandler $cancelHandler
     */
    public function __construct(
        TransactionHistoryService $transactionHistoryService,
        ShopNotificationService $shopNotificationService,
        AuthorizationAdjustmentProxy $authorizationAdjustmentProxy,
        ConnectionService $connectionService,
        CancelHandler $cancelHandler
    ) {
        $this->transactionHistoryService = $transactionHistoryService;
        $this->shopNotificationService = $shopNotificationService;
        $this->authorizationAdjustmentProxy = $authorizationAdjustmentProxy;
        $this->connectionService = $connectionService;
        $this->cancelHandler = $cancelHandler;
    }

    /**
     * Handles extending authorization period.
     *
     * @param string $merchantReference
     *
     * @return bool
     *
     * @throws CurrencyMismatchException
     * @throws InvalidAuthorizationTypeException
     * @throws InvalidMerchantReferenceException
     * @throws InvalidPaymentStateException
     * @throws OrderFullyCapturedException
     * @throws PaymentLinkExistsException
     */
    public function handleExtendingAuthorizationPeriod(string $merchantReference): bool
    {
        $transactionHistory = $this->transactionHistoryService->getTransactionHistory($merchantReference);
        try {
            AuthorizationAdjustmentValidator::validateAdjustmentPossibility($transactionHistory);
            $adjustmentAmount = $transactionHistory->getCapturableAmount();

            return $this->sendAdjustmentRequest($merchantReference, $adjustmentAmount, $transactionHistory);
        } catch (Exception $exception) {
            $this->addHistoryItem($transactionHistory, false, $transactionHistory->getCapturableAmount());
            $this->pushNotification(false, $transactionHistory);

            throw $exception;
        }
    }

    /**
     * @param string $merchantReference
     * @param Amount $amount
     *
     * @return bool
     *
     * @throws CurrencyMismatchException
     * @throws InvalidAuthorizationTypeException
     * @throws InvalidMerchantReferenceException
     * @throws InvalidPaymentStateException
     * @throws OrderFullyCapturedException
     * @throws PaymentLinkExistsException
     * @throws AmountNotChangedException
     * @throws AdjustmentRequestAlreadySentException
     * @throws InvalidAmountException
     */
    public function handleOrderModifications(string $merchantReference, Amount $amount): bool
    {
        $transactionHistory = $this->transactionHistoryService->getTransactionHistory($merchantReference);
        try {
            $amount = $amount->minus($transactionHistory->getCapturedAmount());
            AuthorizationAdjustmentValidator::validateModificationPossibility($transactionHistory, $amount);
            AuthorizationAdjustmentValidator::validateAdjustmentPossibility($transactionHistory);

            if ($amount->getValue() === 0) {
                return $this->cancelHandler->handle($merchantReference);
            }

            return $this->sendAdjustmentRequest($merchantReference, $amount, $transactionHistory);
        } catch (Exception $exception) {
            $this->addHistoryItem($transactionHistory, false, $amount);
            $this->pushNotification(false, $transactionHistory);

            throw $exception;
        }
    }

    /**
     * @param string $merchantReference
     * @param Amount $amount
     * @param TransactionHistory $transactionHistory
     *
     * @return bool
     *
     * @throws InvalidMerchantReferenceException
     */
    private function sendAdjustmentRequest(
        string $merchantReference,
        Amount $amount,
        TransactionHistory $transactionHistory
    ): bool {
        $pspReference = $transactionHistory->getOriginalPspReference();
        $connectionSettings = $this->connectionService->getConnectionData();
        $merchantAccount = $connectionSettings ? $connectionSettings->getActiveConnectionData()->getMerchantId() : '';
        $success = $this->authorizationAdjustmentProxy->adjustPayment(
            new AuthorizationAdjustmentRequest(
                $pspReference,
                $amount,
                $merchantAccount,
                $merchantReference)
        );

        $this->addHistoryItem($transactionHistory, $success, $amount);
        $this->pushNotification($success, $transactionHistory);

        return $success;
    }

    /**
     * Adds new history item to collection.
     *
     * @param TransactionHistory $history
     * @param bool $success
     * @param Amount $amount
     *
     * @return void
     *
     * @throws InvalidMerchantReferenceException
     */
    private function addHistoryItem(TransactionHistory $history, bool $success, Amount $amount): void
    {
        $lastItem = $history->collection()->last();
        $adjustmentRequestCount = count(
            $history->collection()->filterByEventCode(ShopEvents::AUTHORIZATION_ADJUSTMENT_REQUEST)->getAll()
        );
        $history->add(
            new HistoryItem(
                'authorization_adjustment' . ++$adjustmentRequestCount . '_' . $history->getOriginalPspReference(),
                $history->getMerchantReference(),
                ShopEvents::AUTHORIZATION_ADJUSTMENT_REQUEST,
                $lastItem ? $lastItem->getPaymentState() : '',
                TimeProvider::getInstance()->getCurrentLocalTime()->format(DateTimeInterface::ATOM),
                $success,
                $amount,
                $history->getPaymentMethod(),
                $history->getRiskScore(),
                $history->isLive()
            )
        );

        $this->transactionHistoryService->setTransactionHistory($history);
    }

    /**
     * Push shop notification.
     *
     * @param bool $success
     * @param TransactionHistory $history
     *
     * @return void
     */
    private function pushNotification(bool $success, TransactionHistory $history): void
    {
        if ($success) {
            $this->shopNotificationService->pushNotification(
                new SuccessfulAuthorizationAdjustmentRequestEvent($history->getMerchantReference(),
                    $history->getPaymentMethod())
            );

            return;
        }

        $this->shopNotificationService->pushNotification(
            new FailedAuthorizationAdjustmentRequestEvent($history->getMerchantReference(),
                $history->getPaymentMethod())
        );
    }
}
