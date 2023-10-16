<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\PaymentLink\Request;

use Adyen\Core\BusinessLogic\AdminAPI\Request\Request;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Models\PaymentLink;

/**
 * Class PaymentLinkRequest.
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\PaymentLink\Request
 */
class PaymentLinkRequest extends Request
{

    /**
     * @return PaymentLink
     */
    public function transformToDomainModel(): object
    {
    }
}
