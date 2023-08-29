<?php

namespace Adyen\Core\Tests\BusinessLogic\Domain\Webhook\Mocks;

use Adyen\Core\BusinessLogic\Domain\Webhook\Models\WebhookConfig;
use Adyen\Core\BusinessLogic\Domain\Webhook\Models\WebhookRequest;
use Adyen\Core\BusinessLogic\Domain\Webhook\Proxies\WebhookProxy;
use Adyen\Core\Infrastructure\Http\Exceptions\HttpRequestException;

class MockWebhookProxy implements WebhookProxy
{
    public $registerWebhookFails = false;
    public $generateHmacFails = false;

    /**
     * @var string
     */
    public $testResponse = '';

    public function registerWebhook(string $merchantId, WebhookRequest $webhook): WebhookConfig
    {
        if ($this->registerWebhookFails) {
            throw new HttpRequestException('Failed webhook registration.');
        }

        return new WebhookConfig('1234', 'testMerchantId', true, 'username');
    }

    public function generateHMAC(string $merchantId, string $webhookId): string
    {
        if ($this->generateHmacFails) {
            throw new HttpRequestException('Failed hmac generation.');
        }

        return '0123456789';
    }

    public function deleteWebhook(string $merchantId, string $webhookId): void
    {
    }

    public function getWebhookURLs(string $merchantId): array
    {
        return [
            "https://9d3fb2c29721.eu.ngrok.io/frontend/notification/adyen",
            "https://54c75dc75e00.eu.ngrok.io/frontend/notification/adyen.",
            "https://presta1752.marija.eu.ngrok.io",
            "https://1ac72c4dc049.eu.ngrok.io/AdyenWebhook",
            "https://1ac72c4dc049.eu.ngrok.io/AdyenWebhook",
            "https://1ac72c4dc049.eu.ngrok.io/AdyenWebhook",
            "https://4667562cca7d.eu.ngrok.io/frontend/notification/adyen",
            "https://c7431e0ae007.eu.ngrok.io/frontend/notification/adyen",
            "https://c7431e0ae007.eu.ngrok.io/frontend/notification/adyen",
            "https://ps.ad.dev.logeecom.com/module/adyenofficial/Notifications",
            "https://3ff3105ca9db.eu.ngrok.io/module/adyenofficial/Notifications",
            "https://8db02de53e37.eu.ngrok.io/module/adyenofficial/Notifications",
            "https://presta1752.marija.eu.ngrok.io/module/adyenofficial/Notifications",
            "https://presta1752.marija.eu.ngrok.io",
            "https://1-7-7-7.prestashop.sava.eu.ngrok.io/module/adyenofficial/Notifications",
            "https://c41cdd2d6312.eu.ngrok.io/AdyenWebhook/index",
            "https://c41cdd2d6312.eu.ngrok.io/AdyenWebhook/index",
            "https://c41cdd2d6312.eu.ngrok.io/AdyenWebhook/index",
            "https://f808a3763796.ngrok.io/AdyenWebhook/index",
            "https://prestashop.research.dev.logeecom.com",
            "https://d429fdb3c9fc.eu.ngrok.io/AdyenWebhook/index",
            "https://d429fdb3c9fc.eu.ngrok.io/AdyenWebhook/index",
            "https://68810099de9b.eu.ngrok.io/AdyenWebhook/index",
            "https://68810099de9b.eu.ngrok.io/AdyenWebhook/index",
            "https://3db073aedf06.eu.ngrok.io/AdyenWebhook/index",
            "https://3db073aedf06.eu.ngrok.io/AdyenWebhook/index",
            "https://3db073aedf06.eu.ngrok.io/AdyenWebhook/index",
            "https://3db073aedf06.eu.ngrok.io/AdyenWebhook/index",
            "https://37df3912a771.ngrok.io/AdyenWebhook/index",
            "https://37df3912a771.ngrok.io/AdyenWebhook/index",
            "https://37df3912a771.ngrok.io/AdyenWebhook/index",
            "https://shopware57.ad.dev.logeecom.com/AdyenWebhook",
            "https://shopware57.ad.dev.logeecom.com/AdyenWebhook",
            "https://shopware57.ad.dev.logeecom.com/AdyenWebhook",
            "https://shopware57.ad.dev.logeecom.com/AdyenWebhook",
            "https://shopware57.ad.dev.logeecom.com/AdyenWebhook",
            "https://shopware57.ad.dev.logeecom.com/AdyenWebhook",
            "https://shopware57.ad.dev.logeecom.com/AdyenWebhook",
            "https://shopware56.ad.dev.logeecom.com/AdyenWebhook",
            "https://shopware57.ad.dev.logeecom.com/AdyenWebhook",
            "https://shopware57.ad.dev.logeecom.com/AdyenWebhook",
            "https://shopware57.ad.dev.logeecom.com/AdyenWebhook",
            "https://shopware57.ad.dev.logeecom.com/AdyenWebhook",
            "https://shopware57.ad.dev.logeecom.com/AdyenWebhook",
            "https://shopware57.ad.dev.logeecom.com/AdyenWebhook",
            "https://c685743e598f.eu.ngrok.io/AdyenWebhook/index",
            "https://shopware57.ad.dev.logeecom.com/AdyenWebhook",
            "https://shopware57.ad.dev.logeecom.com/AdyenWebhook",
            "https://c685743e598f.eu.ngrok.io/AdyenWebhook/index",
            "https://c685743e598f.eu.ngrok.io/AdyenWebhook/index",
            "https://shopware57.ad.dev.logeecom.com/AdyenWebhook",
            "https://5-7-15.shopware5.sale.eu.ngrok.io/AdyenWebhook",
            "https://5-7-15.shopware5.sale.eu.ngrok.io/AdyenWebhook",
            "https://5-7-15.shopware5.sale.eu.ngrok.io/AdyenWebhook",
            "https://shopware57.ad.dev.logeecom.com/AdyenWebhook"
        ];
    }

    /**
     * @param string $merchantId
     * @param string $url
     *
     * @return array
     */
    public function getWebhookConfigFromUrl(string $merchantId, string $url): array
    {
        return [];
    }

    public function updateWebhook(string $merchantId, string $webhookId, WebhookRequest $webhook): void
    {
    }

    public function testWebhook(string $merchantId, string $webhookId): string
    {
        return $this->testResponse;
    }

    public function setMockTestResponse(string $response): void
    {
        $this->testResponse = $response;
    }
}
