<?php

namespace Adyen\Core\BusinessLogic\WebhookAPI\Response;

use Adyen\Core\BusinessLogic\AdminAPI\Response\Response;

/**
 * Class WebhookSuccessResponse
 *
 * @package Adyen\Core\BusinessLogic\WebhookAPI\Response
 */
class WebhookFailedResponse extends Response
{
    /**
     * @inheritdoc
     */
    protected $successful = false;

    /**
     * @var int
     */
    protected $statusCode = 503;

    /**
     * @var string
     */
    protected $errorMessage;

    /**
     * WebhookFailedResponse constructor.
     *
     * @param string $errorMessage
     */
    public function __construct(string $errorMessage)
    {
        $this->errorMessage = $errorMessage;
    }

    /**
     * @return string[]
     */
    public function toArray(): array
    {
        return [
            'notificationResponse' => '[rejected]',
            'error' => $this->errorMessage
        ];
    }
}
