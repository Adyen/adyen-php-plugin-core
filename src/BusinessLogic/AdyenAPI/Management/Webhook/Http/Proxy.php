<?php

namespace Adyen\Core\BusinessLogic\AdyenAPI\Management\Webhook\Http;

use Adyen\Core\BusinessLogic\AdyenAPI\Http\Authorized\AuthorizedProxy;
use Adyen\Core\BusinessLogic\AdyenAPI\Http\Requests\HttpRequest;
use Adyen\Core\BusinessLogic\AdyenAPI\Management\Webhook\Requests\WebhookHttpRequest;
use Adyen\Core\BusinessLogic\Domain\Webhook\Models\WebhookRequest;
use Adyen\Core\BusinessLogic\Domain\Webhook\Models\WebhookConfig;
use Adyen\Core\BusinessLogic\Domain\Webhook\Proxies\WebhookProxy;
use Adyen\Core\Infrastructure\Http\Exceptions\HttpRequestException;
use Exception;

/**
 * Class Proxy
 *
 * @package Adyen\Core\BusinessLogic\AdyenAPI\MerchantAPI\Webhook\Http
 */
class Proxy extends AuthorizedProxy implements WebhookProxy
{
    private static $retries = 0;
    public const WEBHOOK_TYPE = 'standard';

    /**
     * @inheritDoc
     */
    public function registerWebhook(string $merchantId, WebhookRequest $webhook): WebhookConfig
    {
        $request = new WebhookHttpRequest($webhook, "/merchants/$merchantId/webhooks", ['type' => self::WEBHOOK_TYPE]);

        try{
            $response = $this->post($request)->decodeBodyToArray();
        } catch (Exception $e) {
            return $this->retry($merchantId, $webhook, $e);
        }

        static::$retries = 0;

        return new WebhookConfig(
            $response['id'] ?? '',
            $merchantId,
            $response['active'] ?? false,
            $response['username'] ?? ''
        );
    }

    /**
     * @inheritDoc
     */
    public function deleteWebhook(string $merchantId, string $webhookId): void
    {
        $request = new HttpRequest("/merchants/$merchantId/webhooks/$webhookId");

        $this->delete($request);
    }

    /**
     * @inheritDoc
     */
    public function generateHMAC(string $merchantId, string $webhookId): string
    {
        $request = new HttpRequest("/merchants/$merchantId/webhooks/$webhookId/generateHmac");

        try {
            $response = $this->post($request)->decodeBodyToArray();
        } catch (Exception $e) {
            return $this->retryHMAC($merchantId, $webhookId, $e);
        }

        static::$retries = 0;

        return $response['hmacKey'] ?? '';
    }

    /**
     * @inheritDoc
     *
     * @throws HttpRequestException
     */
    public function getWebhookURLs(string $merchantId): array
    {
        $page = 1;
        $response = $this->getWebhooks($merchantId, $page);
        $result = $this->getWebhookUrlsFromResponse($response['data'] ?? []);

        while (!empty($response['data'])) {
            $page++;
            $response = $this->getWebhooks($merchantId, $page);
            /** @noinspection SlowArrayOperationsInLoopInspection */
            $result = array_merge($result, $this->getWebhookUrlsFromResponse($response['data'] ?? []));
        }

        return $result;
    }

    /**
     * @param string $merchantId
     * @param string $url
     *
     * @return array
     * @throws HttpRequestException
     */
    public function getWebhookConfigFromUrl(string $merchantId, string $url): array
    {
        $page = 1;
        $response = $this->getWebhooks($merchantId, $page);
        $result = $this->getWebhookConfig($response, $url);

        while (!empty($response['data']) && empty($result)) {
            $page++;
            $response = $this->getWebhooks($merchantId, $page);
            $result = $this->getWebhookConfig($response, $url);
        }

        return $result;
    }

    /**
     * @param string $merchantId
     * @param string $webhookId
     *
     * @param WebhookRequest $webhook
     *
     * @return void
     *
     * @throws HttpRequestException
     */
    public function updateWebhook(string $merchantId, string $webhookId, WebhookRequest $webhook): void
    {
        $request = new WebhookHttpRequest($webhook, "/merchants/$merchantId/webhooks/$webhookId");

        $this->patch($request);
    }

    /**
     * @param string $merchantId
     * @param string $webhookId
     *
     * @return string
     *
     * @throws HttpRequestException
     */
    public function testWebhook(string $merchantId, string $webhookId): string
    {
        $request = new HttpRequest("/merchants/$merchantId/webhooks/$webhookId/test", $this->testWebhookBody());

        try {
            $response = $this->post($request)->getBody();
        } catch (Exception $e) {
            return $this->retryTest($merchantId, $webhookId, $e);
        }

        static::$retries = 0;

        return $response ?? '';
    }

    /**
     * @param string $merchantId
     * @param int $page
     *
     * @return array
     *
     * @throws HttpRequestException
     */
    protected function getWebhooks(string $merchantId, int $page): array
    {
        $request = new HttpRequest(
            "/merchants/$merchantId/webhooks",
            [],
            [
                'pageNumber' => $page,
                'pageSize' => 100
            ]
        );

        return $this->get($request)->decodeBodyToArray();
    }

    /**
     * @param string $merchantId
     * @param WebhookRequest $webhookRequest
     * @param Exception $e
     *
     * @return WebhookConfig
     * @throws Exception
     */
    private function retry(string $merchantId, WebhookRequest $webhookRequest, Exception $e): WebhookConfig
    {
        if (static::$retries >= 3) {
            static::$retries = 0;

            throw $e;
        }

        sleep(1);
        static::$retries = static::$retries + 1;

        return $this->registerWebhook($merchantId, $webhookRequest);
    }

    /**
     * @param string $merchantId
     * @param string $webhookId
     * @param Exception $e
     *
     * @return string
     *
     * @throws Exception
     */
    private function retryHMAC(string $merchantId, string $webhookId, Exception $e): string
    {
        if (static::$retries >= 3) {
            static::$retries = 0;

            throw $e;
        }

        sleep(1);
        static::$retries = static::$retries + 1;

        return $this->generateHMAC($merchantId, $webhookId);
    }

    /**
     * @param string $merchantId
     * @param string $webhookId
     * @param Exception $e
     *
     * @return string
     * @throws HttpRequestException
     */
    private function retryTest(string $merchantId, string $webhookId, Exception $e): string
    {
        if (static::$retries >= 3) {
            static::$retries = 0;

            throw $e;
        }

        sleep(1);
        static::$retries = static::$retries + 1;

        return $this->testWebhook($merchantId, $webhookId);
    }

    /**
     * @param array $response
     * @param string $url
     *
     * @return array
     */
    private function getWebhookConfig(array $response, string $url): array
    {
        if (empty($response['data'])) {
            return [];
        }

        foreach ($response['data'] as $data) {
            if ($data['url'] === $url) {
                return [
                    'id' => $data['id'],
                    'username' => $data['username'],
                    'active' => $data['active']
                ];
            }
        }

        return [];
    }

    /**
     * @param array $response
     *
     * @return array
     */
    private function getWebhookUrlsFromResponse(array $response): array
    {
        $urls = [];

        foreach ($response as $item) {
            $urls[] = $item['url'] ?? '';
        }

        return $urls;
    }

    /**
     * @return array
     */
    private function testWebhookBody(): array
    {
        return [
            "notification" => [
                "paymentMethod" => "visa",
                "eventCode" => "AUTHORISATION",
                "amount" => "10",
                "reason" => "Authorize visa payment",
                "success" => true
            ],
            "types" => ["CUSTOM", "AUTHORISATION"]
        ];
    }
}
