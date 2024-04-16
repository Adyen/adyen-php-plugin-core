<?php

namespace Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Validator;

use Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Exceptions\AdjustmentRequestAlreadySentException;
use Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Exceptions\AmountNotChangedException;
use Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Exceptions\InvalidAmountException;
use Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Exceptions\InvalidAuthorizationTypeException;
use Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Exceptions\InvalidPaymentStateException;
use Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Exceptions\OrderFullyCapturedException;
use Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Exceptions\PaymentLinkExistsException;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\CurrencyMismatchException;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\AuthorizationType;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\ShopEvents;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory;
use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class AuthorizationAdjustmentValidator.
 *
 * @package Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Validator
 */
class AuthorizationAdjustmentValidator
{
    /**
     * @throws InvalidAuthorizationTypeException
     * @throws CurrencyMismatchException
     * @throws OrderFullyCapturedException
     * @throws InvalidPaymentStateException
     * @throws PaymentLinkExistsException
     */
    public static function validateAdjustmentPossibility(TransactionHistory $transactionHistory): void
    {
        if (!$transactionHistory->getAuthorizationType() ||
            !$transactionHistory->getAuthorizationType()->equal(AuthorizationType::preAuthorization())) {
            throw new InvalidAuthorizationTypeException(
                new TranslatableLabel(
                    'Authorization adjustment is only possible for payments with Pre-authorization authorization types',
                    'authorizationAdjustment.invalidAuthorizationType')
            );
        }

        if ($transactionHistory->getCapturableAmount()->getValue() <= 0) {
            throw new OrderFullyCapturedException(new TranslatableLabel(
                'Authorization adjustment is not possible. Order is fully captured.',
                'authorizationAdjustment.orderFullyCaptured'));
        }

        if (!$transactionHistory->collection()->filterByEventCode('CANCELLATION')->filterByStatus(true)->isEmpty()) {
            throw new InvalidPaymentStateException(new TranslatableLabel('Authorization adjustment is not possible. Order is cancelled.',
                'authorizationAdjustment.orderCancelled'));
        }

        if ($transactionHistory->getPaymentLink()) {
            throw new PaymentLinkExistsException(new TranslatableLabel('Authorization adjustment is not possible. Payment link is generated.',
                'authorizationAdjustment.paymentLink'));
        }
    }

    /**
     * @param TransactionHistory $transactionHistory
     * @param Amount $amount
     *
     * @return void
     *
     * @throws AmountNotChangedException
     * @throws CurrencyMismatchException
     * @throws AdjustmentRequestAlreadySentException
     * @throws InvalidAmountException
     */
    public static function validateModificationPossibility(TransactionHistory $transactionHistory, Amount $amount): void
    {
        if ($amount->getValue() < 0) {
            throw new InvalidAmountException(
                new TranslatableLabel(
                    'Authorization modification is not possible. Amount is less that zero.',
                    'authorizationAdjustment.invalidAmount')
            );
        }

        if ($transactionHistory->getAuthorizedAmount()->getValue() === $amount->getValue()) {
            throw new AmountNotChangedException(
                new TranslatableLabel(
                    'Authorization modification is not possible. Amount is not changed.',
                    'authorizationAdjustment.amountNotChanged')
            );
        }

        if ($transactionHistory->collection()->filterByEventCode(ShopEvents::AUTHORIZATION_ADJUSTMENT_REQUEST)
                ->filterByStatus(true)->last() &&
            $transactionHistory->collection()->filterByEventCode(ShopEvents::AUTHORIZATION_ADJUSTMENT_REQUEST)
                ->filterByStatus(true)->last()->getAmount()->getValue() === $amount->getValue()) {
            throw new AdjustmentRequestAlreadySentException(
                new TranslatableLabel(
                    'Authorization modification request is already sent.',
                    'authorizationAdjustment.adjustmentRequestAlreadySent')
            );
        }
    }
}
