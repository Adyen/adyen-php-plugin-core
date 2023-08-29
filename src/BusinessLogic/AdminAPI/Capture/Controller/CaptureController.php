<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\Capture\Controller;

use Adyen\Core\BusinessLogic\AdminAPI\Capture\Response\CaptureResponse;
use Adyen\Core\BusinessLogic\Domain\Capture\Handlers\CaptureHandler;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\InvalidCurrencyCode;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Currency;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Exceptions\InvalidMerchantReferenceException;

/**
 * Class CaptureController
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\Capture\Controller
 */
class CaptureController
{
    /**
     * @var CaptureHandler
     */
    private $handler;

    /**
     * @param CaptureHandler $captureHandler
     */
    public function __construct(CaptureHandler $captureHandler)
    {
        $this->handler = $captureHandler;
    }

    /**
     * @param string $merchantReference
     * @param float $value
     * @param string $currency
     *
     * @return CaptureResponse True if capture request was received by Adyen successfully; false otherwise. Use transaction log
     * for final action outcome.
     *
     * @throws InvalidMerchantReferenceException
     * @throws InvalidCurrencyCode
     */
    public function handle(string $merchantReference, float $value, string $currency): CaptureResponse
    {
        return new CaptureResponse(
            $this->handler->handle($merchantReference, Amount::fromFloat($value, Currency::fromIsoCode($currency)))
        );
    }
}
