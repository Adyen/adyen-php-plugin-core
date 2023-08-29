<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\OrderMappings\Response;

use Adyen\Core\BusinessLogic\AdminAPI\Response\Response;

/**
 * Class OrderMappingsPutResponse
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\OrderMappings\Response
 */
class OrderMappingsPutResponse extends Response
{
    /**
     * @return array
     */
    public function toArray(): array
    {
        return ['success' => true];
    }
}
