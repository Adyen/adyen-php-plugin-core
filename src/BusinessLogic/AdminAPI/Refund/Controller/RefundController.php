<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\Refund\Controller;

use Adyen\Core\BusinessLogic\AdminAPI\Refund\Response\RefundResponse;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\InvalidCurrencyCode;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Currency;
use Adyen\Core\BusinessLogic\Domain\Refund\Handlers\RefundHandler;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Exceptions\InvalidMerchantReferenceException;

/**
 * Class RefundController
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\Refund\Controller
 */
class RefundController
{
    /**
     * @var RefundHandler
     */
    private $handler;

    /**
     * @param RefundHandler $refundHandler
     */
    public function __construct(RefundHandler $refundHandler)
    {
        $this->handler = $refundHandler;
    }

    /**
     * @param string $merchantReference
     * @param float $value
     * @param string $currency
     *
     * @return RefundResponse True if refund request was received by Adyen successfully; false otherwise. Use transaction log
     * for final action outcome.
     *
     * @throws InvalidMerchantReferenceException
     * @throws InvalidCurrencyCode
     */
    public function handle(string $merchantReference, float $value, string $currency): RefundResponse
    {
        return new RefundResponse(
            $this->handler->handle($merchantReference, Amount::fromFloat($value, Currency::fromIsoCode($currency)))
        );
    }
}
