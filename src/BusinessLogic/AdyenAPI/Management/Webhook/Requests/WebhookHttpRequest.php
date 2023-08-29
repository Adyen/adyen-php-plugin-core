<?php

namespace Adyen\Core\BusinessLogic\AdyenAPI\Management\Webhook\Requests;

use Adyen\Core\BusinessLogic\AdyenAPI\Http\Requests\HttpRequest;
use Adyen\Core\BusinessLogic\Domain\Webhook\Models\WebhookRequest;

/**
 * Class WebhookHttpRequest
 *
 * @package Adyen\Core\BusinessLogic\AdyenAPI\MerchantAPI\Webhook\Requests
 */
class WebhookHttpRequest extends HttpRequest
{
    public const COMMUNICATION_FORMAT = 'json';
    /**
     * @var WebhookRequest
     */
    private $request;

    public function __construct(WebhookRequest $request, string $endpoint, array $body = [])
    {
        $this->request = $request;
        parent::__construct($endpoint, array_merge($this->transformBody(), $body));
    }

    /**
     * Transforms webhook request to array.
     *
     * @return array
     */
    public function transformBody(): array
    {
        return [
            'url' => $this->request->getUrl(),
            'username' => $this->request->getUsername(),
            'password' => $this->request->getPassword(),
            'active' => true,
            'communicationFormat' => self::COMMUNICATION_FORMAT
        ];
    }
}
