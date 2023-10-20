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
     * @return array
     */
    public function toArray(): array
    {
        return ['success' => true];
    }
}
