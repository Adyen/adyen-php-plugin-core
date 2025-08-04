<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\Connection\Response;

use Adyen\Core\BusinessLogic\AdminAPI\Response\Response;

/**
 * Class ReRegisterWebhookResponse.
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\Connection\Response
 */
class ReRegisterWebhookResponse extends Response
{
    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return ['status' => true];
    }
}
