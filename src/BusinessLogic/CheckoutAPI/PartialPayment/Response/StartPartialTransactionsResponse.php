<?php

namespace Adyen\Core\BusinessLogic\CheckoutAPI\PartialPayment\Response;

use Adyen\Core\BusinessLogic\AdminAPI\Response\Response;
use Adyen\Core\BusinessLogic\CheckoutAPI\PaymentRequest\Response\StartTransactionResponse;

/**
 * Class StartPartialTransactionsResponse
 *
 * @package Adyen\Core\BusinessLogic\CheckoutAPI\PaymentRequest\Response
 */
class StartPartialTransactionsResponse extends Response
{
    /**
     * @var StartTransactionResponse
     */
    private $latestTransactionResponse;

    public function __construct(StartTransactionResponse $latestTransactionResponse)
    {
        $this->latestTransactionResponse = $latestTransactionResponse;
    }

    /**
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return $this->latestTransactionResponse->isSuccessful();
    }

    /**
     * Returns true if additional action is required for the created payment transaction.
     * Use @see self::getAction() to fetch action configuration that should be triggered with the web components
     *
     * @return bool True if additional action is required; false otherwise.
     */
    public function isAdditionalActionRequired(): bool
    {
        return $this->latestTransactionResponse->isAdditionalActionRequired();
    }

    /**
     * @return StartTransactionResponse
     */
    public function getLatestTransactionResponse(): StartTransactionResponse
    {
        return $this->latestTransactionResponse;
    }

    public function toArray(): array
    {
       return [
            'latestTransactionResponse' => $this->latestTransactionResponse->toArray(),
       ];
    }
}
