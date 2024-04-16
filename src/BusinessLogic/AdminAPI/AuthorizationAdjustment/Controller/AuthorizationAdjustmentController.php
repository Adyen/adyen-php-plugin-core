<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\AuthorizationAdjustment\Controller;

use Adyen\Core\BusinessLogic\AdminAPI\AuthorizationAdjustment\Response\AuthorizationAdjustmentResponse;
use Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Exceptions\AdjustmentRequestAlreadySentException;
use Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Exceptions\AmountNotChangedException;
use Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Exceptions\InvalidAmountException;
use Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Exceptions\InvalidAuthorizationTypeException;
use Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Exceptions\InvalidPaymentStateException;
use Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Exceptions\OrderFullyCapturedException;
use Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Exceptions\PaymentLinkExistsException;
use Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Handlers\AuthorizationAdjustmentHandler;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\CurrencyMismatchException;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\InvalidCurrencyCode;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Currency;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Exceptions\InvalidMerchantReferenceException;

/**
 * Class AuthorizationAdjustmentController.
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\AuthorizationAdjustment\Controller
 */
class AuthorizationAdjustmentController
{
    /**
     * @var AuthorizationAdjustmentHandler
     */
    private $handler;

    /**
     * @param AuthorizationAdjustmentHandler $authorizationAdjustmentHandler
     */
    public function __construct(AuthorizationAdjustmentHandler $authorizationAdjustmentHandler)
    {
        $this->handler = $authorizationAdjustmentHandler;
    }

    /**
     * @param string $merchantReference
     *
     * @return AuthorizationAdjustmentResponse True if adjustment request was received by Adyen successfully; false
     * otherwise.
     *
     * @throws InvalidAuthorizationTypeException
     * @throws InvalidPaymentStateException
     * @throws OrderFullyCapturedException
     * @throws PaymentLinkExistsException
     * @throws CurrencyMismatchException
     * @throws InvalidMerchantReferenceException
     */
    public function handleExtendingAuthorizationPeriod(string $merchantReference): AuthorizationAdjustmentResponse
    {
        return new AuthorizationAdjustmentResponse($this->handler->handleExtendingAuthorizationPeriod($merchantReference));
    }

    /**
     * @param string $merchantReference
     * @param float $value
     * @param string $currency
     *
     * @return AuthorizationAdjustmentResponse True if modification request was received by Adyen successfully; false
     * otherwise.
     *
     * @throws CurrencyMismatchException
     * @throws InvalidAuthorizationTypeException
     * @throws InvalidMerchantReferenceException
     * @throws InvalidPaymentStateException
     * @throws OrderFullyCapturedException
     * @throws PaymentLinkExistsException
     * @throws AdjustmentRequestAlreadySentException
     * @throws AmountNotChangedException
     * @throws InvalidCurrencyCode
     * @throws InvalidAmountException
     */
    public function handleOrderModification(
        string $merchantReference,
        float  $value,
        string $currency
    ): AuthorizationAdjustmentResponse {
        return new AuthorizationAdjustmentResponse($this->handler->handleOrderModifications(
            $merchantReference,
            Amount::fromFloat($value, Currency::fromIsoCode($currency)))
        );
    }
}
