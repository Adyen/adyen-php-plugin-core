<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\InfoSettings\Response;

use Adyen\Core\BusinessLogic\AdminAPI\Response\Response;

/**
 * Class WebhookValidationResponse
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\InfoSettings\Response
 */
class WebhookValidationResponse extends Response
{
    /**
     * Message for successful webhook validation.
     */
    private const SUCCESS_MESSAGE = 'Webhook validated successfully.';

    /**
     * Message for failed webhook validation.
     */
    private const FAIL_MESSAGE = 'Store was not able to receive webhook from Adyen.';

    /**
     * @var bool
     */
    private $success;

    /**
     * @param bool $success
     */
    public function __construct(bool $success)
    {
        $this->success = $success;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'status' => $this->success,
            'message' => $this->success ? 'webhook.validation.success' : 'webhook.validation.fail'
        ];
    }
}
