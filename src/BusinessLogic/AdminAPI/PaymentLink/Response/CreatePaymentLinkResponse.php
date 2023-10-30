<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\PaymentLink\Response;

use Adyen\Core\BusinessLogic\AdminAPI\Response\Response;

/**
 * Class PaymentLinkResponse
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\PaymentLink\Response
 */
class CreatePaymentLinkResponse extends Response
{
    /**
     * @var string
     */
    private $paymentLink;

    /**
     * @param string $paymentLink
     */
    public function __construct(string $paymentLink)
    {
        $this->paymentLink = $paymentLink;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return ['success' => true, 'paymentLink' => $this->paymentLink];
    }
}
