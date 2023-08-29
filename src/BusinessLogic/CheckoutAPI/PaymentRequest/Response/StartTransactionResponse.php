<?php

namespace Adyen\Core\BusinessLogic\CheckoutAPI\PaymentRequest\Response;

use Adyen\Core\BusinessLogic\AdminAPI\Response\Response;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\StartTransactionResult;

/**
 * Class CreateTransactionResponse
 *
 * @package Adyen\Core\BusinessLogic\CheckoutAPI\PaymentRequest\Response
 */
class StartTransactionResponse extends Response
{
    /**
     * @var StartTransactionResult
     */
    private $result;

    public function __construct(StartTransactionResult $result)
    {

        $this->result = $result;
    }

    /**
     * Gets the Adyen's PSP reference for payment transaction or null if PSP reference does not exist.
     *
     * @return string|null
     */
    public function getPspReference(): ?string
    {
        return $this->result->getPspReference();
    }

    public function isSuccessful(): bool
    {
        return $this->result->getResultCode()->isSuccessful();
    }

    /**
     * Returns true if additional action is required for the created payment transaction.
     * Use @see self::getAction() to fetch action configuration that should be triggered with the web components
     *
     * @return bool True if additional action is required; false otherwise.
     */
    public function isAdditionalActionRequired(): bool
    {
        return null !== $this->result->getAction();
    }

    /**
     * Gets the additional action object that should be triggered with the web components.
     *
     * @return mixed|null Additional action configuration or null if no action is required.
     */
    public function getAction()
    {
        return $this->result->getAction();
    }

    public function shouldPresentToShopper(): bool
    {
        return $this->result->getResultCode()->shouldPresentToShopper();
    }

    public function isRecieved(): bool
    {
        return $this->result->getResultCode()->isRecieved();
    }

    public function toArray(): array
    {
        $responseData = [
            'resultCode' => $this->result->getResultCode(),
        ];

        if ($this->result->getAction()) {
            $responseData['action'] = $this->result->getAction();
        }

        if ($this->result->getPspReference()) {
            $responseData['pspReference'] = $this->result->getPspReference();
        }

        if ($this->result->getDonationToken()) {
            $responseData['donationToken'] = $this->result->getDonationToken();
        }

        return $responseData;
    }
}
