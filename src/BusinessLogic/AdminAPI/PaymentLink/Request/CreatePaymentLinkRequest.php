<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\PaymentLink\Request;

use Adyen\Core\BusinessLogic\AdminAPI\Request\Request;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Models\PaymentLinkRequestContext;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\InvalidCurrencyCode;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Currency;
use DateTime;

/**
 * Class PaymentLinkRequest.
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\PaymentLink\Request
 */
class CreatePaymentLinkRequest extends Request
{
    /**
     * @var string
     */
    private $amount;
    /**
     * @var string
     */
    private $currency;
    /**
     * @var string
     */
    private $reference;
    /**
     * @var DateTime
     */
    private $expiresAt;

    /**
     * @param float $amount
     * @param string $currency
     * @param string $reference
     * @param DateTime|null $expiresAt
     */
    public function __construct(
        float $amount,
        string $currency,
        string $reference,
        DateTime $expiresAt = null
    ) {
        $this->amount = $amount;
        $this->currency = $currency;
        $this->reference = $reference;
        $this->expiresAt = $expiresAt;
    }

    /**
     * @return PaymentLinkRequestContext
     *
     * @throws InvalidCurrencyCode
     */
    public function transformToDomainModel(): object
    {
        return new PaymentLinkRequestContext(
            Amount::fromFloat($this->amount, Currency::fromIsoCode($this->currency)),
            $this->reference,
            $this->expiresAt
        );
    }
}
