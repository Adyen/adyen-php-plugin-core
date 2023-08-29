<?php

namespace Adyen\Core\BusinessLogic\CheckoutAPI\CheckoutConfig\Response;

use Adyen\Core\BusinessLogic\AdminAPI\Response\Response;

/**
 * Class DisableStoredDetailsResponse
 *
 * @package Adyen\Core\BusinessLogic\CheckoutAPI\CheckoutConfig\Response
 */
class DisableStoredDetailsResponse extends Response
{

    public function toArray(): array
    {
        return [];
    }
}
