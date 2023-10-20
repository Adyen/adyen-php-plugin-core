<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\PaymentLink\Request;

use Adyen\Core\BusinessLogic\AdminAPI\Request\Request;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Models\PaymentLinkRequestContext;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use DateTime;

/**
 * Class PaymentLinkRequest.
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\PaymentLink\Request
 */
class CreatePaymentLinkRequest extends Request
{
    /**
     * @var Amount
     */
    private $amount;
    /**
     * @var string
     */
    private $reference;

    /**
     * @var DateTime
     */
    private $expiresAt;

    /**
     * @param Amount $amount
     * @param string $reference
     * @param DateTime|null $expiresAt
     */
    public function __construct(
        Amount $amount,
        string $reference,
        DateTime $expiresAt = null
    ) {
        $this->amount = $amount;
        $this->reference = $reference;
        $this->expiresAt = $expiresAt;
    }

    /**
     * @return PaymentLinkRequestContext
     */
    public function transformToDomainModel(): object
    {
        return new PaymentLinkRequestContext($this->amount, $this->reference, $this->expiresAt);
    }
}
