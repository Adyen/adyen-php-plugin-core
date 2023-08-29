<?php

namespace Adyen\Core\BusinessLogic\WebhookAPI\Response;

use Adyen\Core\BusinessLogic\AdminAPI\Response\Response;

/**
 * Class WebhookSuccessResponse
 *
 * @package Adyen\Core\BusinessLogic\WebhookAPI\Response
 */
class WebhookSuccessResponse extends Response
{
    /**
     * @return string[]
     */
    public function toArray(): array
    {
        return ["notificationResponse" => "[accepted]"];
    }
}
