<?php

namespace Adyen\Core\BusinessLogic\Domain\Integration\Webhook;

/**
 * Class WebhookUrlService
 *
 * @package Adyen\Core\BusinessLogic\Domain\Integration\Webhook
 */
interface WebhookUrlService
{

    public function getWebhookUrl();
}
