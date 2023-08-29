<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\InfoSettings\Response;

use Adyen\Core\BusinessLogic\AdminAPI\Response\Response;

/**
 * Class DebugPutResponse
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\InfoSettings\Response
 */
class DebugPutResponse extends Response
{
    /**
     * @return array
     */
    public function toArray(): array
    {
        return ['success' => true];
    }
}
