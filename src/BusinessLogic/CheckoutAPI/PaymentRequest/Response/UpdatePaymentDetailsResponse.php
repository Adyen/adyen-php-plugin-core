<?php

namespace Adyen\Core\BusinessLogic\CheckoutAPI\PaymentRequest\Response;

use Adyen\Core\BusinessLogic\AdminAPI\Response\Response;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\UpdatePaymentDetailsResult;

/**
 * Class UpdatePaymentDetailsResponse
 *
 * @package Adyen\Core\BusinessLogic\CheckoutAPI\PaymentRequest\Response
 */
class UpdatePaymentDetailsResponse extends Response
{
    /**
     * @var UpdatePaymentDetailsResult
     */
    private $result;

    public function __construct(UpdatePaymentDetailsResult $result)
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

    public function toArray(): array
    {
        $responseData = [
            'resultCode' => (string)$this->result->getResultCode(),
        ];

        if ($this->result->getPspReference()) {
            $responseData['pspReference'] = $this->result->getPspReference();
        }

        return $responseData;
    }
}
