<?php

namespace Adyen\Core\Tests\BusinessLogic\Domain\Webhook\Mocks;

use Adyen\Core\BusinessLogic\Domain\Integration\Webhook\WebhookUrlService;

class MockWebhookUrlService implements WebhookUrlService
{
    /**
     * @var string
     */
    private $url;

    public function __construct()
    {
        $this->url = '';
    }

    /**
     * @return string
     */
    public function getWebhookUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return void
     */
    public function setUrl(string $url) {
        $this->url = $url;
    }
}
