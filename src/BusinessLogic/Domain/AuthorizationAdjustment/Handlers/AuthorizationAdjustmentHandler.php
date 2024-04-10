<?php

namespace Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Handlers;

use Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Exceptions\InvalidAuthorizationTypeException;
use Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Exceptions\InvalidPaymentStateException;
use Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Exceptions\OrderFullyCapturedException;
use Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Exceptions\PaymentLinkExistsException;
use Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Models\AuthorizationAdjustmentRequest;
use Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Proxies\AuthorizationAdjustmentProxy;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\CurrencyMismatchException;
use Adyen\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\AuthorizationType;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\AuthorizationAdjustment\FailedAuthorizationAdjustmentRequestEvent;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\AuthorizationAdjustment\SuccessfulAuthorizationAdjustmentRequestEvent;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\ShopEvents;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Services\ShopNotificationService;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Exceptions\InvalidMerchantReferenceException;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models\HistoryItem;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Services\TransactionHistoryService;
use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
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
     * @param TransactionHistoryService $transactionHistoryService
     * @param ShopNotificationService $shopNotificationService
     * @param AuthorizationAdjustmentProxy $authorizationAdjustmentProxy
     * @param ConnectionService $connectionService
     */
    public function __construct(
        TransactionHistoryService $transactionHistoryService,
        ShopNotificationService $shopNotificationService,
        AuthorizationAdjustmentProxy $authorizationAdjustmentProxy,
        ConnectionService $connectionService
    ) {
        $this->transactionHistoryService = $transactionHistoryService;
        $this->shopNotificationService = $shopNotificationService;
        $this->authorizationAdjustmentProxy = $authorizationAdjustmentProxy;
        $this->connectionService = $connectionService;
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
            $this->validateAdjustmentPossibility($transactionHistory);
            $adjustmentAmount = $transactionHistory->getAuthorizedAmount()->minus($transactionHistory->getCapturedAmount());
            $pspReference = $transactionHistory->getOriginalPspReference();
            $connectionSettings = $this->connectionService->getConnectionData();
            $merchantAccount = $connectionSettings ? $connectionSettings->getActiveConnectionData()->getMerchantId() : '';
            $success = $this->authorizationAdjustmentProxy->adjustPayment(
                new AuthorizationAdjustmentRequest(
                    $pspReference,
                    $adjustmentAmount,
                    $merchantAccount,
                    $merchantReference)
            );

            $this->addHistoryItem($transactionHistory, $success);
            $this->pushNotification($success, $transactionHistory);

            return $success;
        } catch (Exception $exception) {
            $this->addHistoryItem($transactionHistory, false);
            $this->pushNotification(false, $transactionHistory);

            throw $exception;
        }
    }

    /**
     * @throws InvalidAuthorizationTypeException
     * @throws CurrencyMismatchException
     * @throws OrderFullyCapturedException
     * @throws InvalidPaymentStateException
     * @throws PaymentLinkExistsException
     */
    private function validateAdjustmentPossibility(TransactionHistory $transactionHistory): void
    {
        if (!$transactionHistory->getAuthorizationType() ||
            !$transactionHistory->getAuthorizationType()->equal(AuthorizationType::preAuthorization())) {
            throw new InvalidAuthorizationTypeException(
                new TranslatableLabel(
                    'Authorization adjustment is only possible for payments with Pre-authorization authorization types',
                    'authorizationAdjustment.invalidAuthorizationType')
            );
        }

        if ($transactionHistory->getCapturedAmount()->minus($transactionHistory->getAuthorizedAmount())->getPriceInCurrencyUnits() === 0) {
            throw new OrderFullyCapturedException(new TranslatableLabel(
                'Authorization adjustment is not possible. Order is fully captured.',
                'authorizationAdjustment.orderFullyCaptured'));
        }

        if ($transactionHistory->collection()->last()->getPaymentState() === 'cancelled') {
            throw new InvalidPaymentStateException(new TranslatableLabel('Authorization adjustment is not possible. Order is cancelled.',
                'authorizationAdjustment.orderCancelled'));
        }

        if ($transactionHistory->getPaymentLink()) {
            throw new PaymentLinkExistsException(new TranslatableLabel('Authorization adjustment is not possible. Payment link is generated.',
                'authorizationAdjustment.paymentLink'));
        }
    }

    /**
     * Adds new history item to collection.
     *
     * @param TransactionHistory $history
     * @param bool $success
     *
     * @return void
     *
     * @throws InvalidMerchantReferenceException
     * @throws CurrencyMismatchException
     */
    private function addHistoryItem(TransactionHistory $history, bool $success): void
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
                $history->getAuthorizedAmount()->minus($history->getCapturedAmount()),
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
