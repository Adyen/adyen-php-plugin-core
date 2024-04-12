<?php

namespace Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Validator;

use Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Exceptions\InvalidAuthorizationTypeException;
use Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Exceptions\InvalidPaymentStateException;
use Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Exceptions\OrderFullyCapturedException;
use Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Exceptions\PaymentLinkExistsException;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\CurrencyMismatchException;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\AuthorizationType;
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

        if ($transactionHistory->collection()->last()->getPaymentState() === 'cancelled') {
            throw new InvalidPaymentStateException(new TranslatableLabel('Authorization adjustment is not possible. Order is cancelled.',
                'authorizationAdjustment.orderCancelled'));
        }

        if ($transactionHistory->getPaymentLink()) {
            throw new PaymentLinkExistsException(new TranslatableLabel('Authorization adjustment is not possible. Payment link is generated.',
                'authorizationAdjustment.paymentLink'));
        }
    }
}
