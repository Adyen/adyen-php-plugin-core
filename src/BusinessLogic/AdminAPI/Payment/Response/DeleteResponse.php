<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\Payment\Response;

use Adyen\Core\BusinessLogic\AdminAPI\Response\Response;

/**
 * Class DeleteResponse
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\Payment\Response
 */
class DeleteResponse extends Response
{
    public function toArray(): array
    {
        return [];
    }
}
