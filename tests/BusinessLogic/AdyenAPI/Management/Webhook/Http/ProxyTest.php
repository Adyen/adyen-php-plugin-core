<?php

namespace Adyen\Core\Tests\BusinessLogic\AdyenAPI\Management\Webhook\Http;

use Adyen\Core\BusinessLogic\AdyenAPI\Management\ProxyFactory;
use Adyen\Core\BusinessLogic\AdyenAPI\Management\Webhook\Http\Proxy;
use Adyen\Core\BusinessLogic\DataAccess\Connection\Entities\ConnectionSettings as ConnectionSettingsEntity;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionSettings;
use Adyen\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Adyen\Core\BusinessLogic\Domain\Webhook\Models\WebhookRequest;
use Adyen\Core\Infrastructure\Http\Exceptions\HttpRequestException;
use Adyen\Core\Infrastructure\Http\HttpClient;
use Adyen\Core\Infrastructure\Http\HttpResponse;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\TestHttpClient;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;

class ProxyTest extends BaseTestCase
{
    /**
     * @var Proxy
     */
    public $proxy;
    /**
     * @var TestHttpClient
     */
    public $httpClient;

    protected function setUp(): void
    {
        parent::setUp();

        $repository = TestRepositoryRegistry::getRepository(ConnectionSettingsEntity::getClassName());
        $factory = new ProxyFactory();

        $settings = new ConnectionSettings(
            '1',
            'test',
            new ConnectionData('1234567890', '1111'),
            null
        );
        $settingsEntity = new ConnectionSettingsEntity();
        $settingsEntity->setConnectionSettings($settings);
        $repository->save($settingsEntity);
        $httpClient = TestServiceRegister::getService(HttpClient::class);
        $this->httpClient = $httpClient;
        TestServiceRegister::registerService(HttpClient::class, function () use ($httpClient) {
            return $httpClient;
        });

        $this->proxy = StoreContext::doWithStore('1', [$factory, 'makeProxy'], [Proxy::class]);
    }

    public function testRegisterWebhookUrl(): void
    {
        // arrange
        $this->httpClient->setMockResponses([
            new HttpResponse(
                200,
                [],
                file_get_contents(__DIR__ . '/../../../../Common/ApiResponses/Webhook/registerWebhook.json')
            )
        ]);
        $webhookRequest = new WebhookRequest(
            'standard',
            'teststore.test',
            'username'
        );

        // act
        $this->proxy->registerWebhook('1234', $webhookRequest);

        // assert
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertEquals(
            'https://' . ProxyFactory::MANAGEMENT_API_TEST_URL . '/v1/merchants/1234/webhooks',
            $lastRequest['url']
        );
    }

    public function testRegisterWebhookMethod(): void
    {
        // arrange
        $this->httpClient->setMockResponses([
            new HttpResponse(
                200,
                [],
                file_get_contents(__DIR__ . '/../../../../Common/ApiResponses/Webhook/registerWebhook.json')
            )
        ]);
        $webhookRequest = new WebhookRequest(
            'standard',
            'teststore.test',
            'username',
            'password',
            true,
            'json'
        );

        // act
        $this->proxy->registerWebhook('1234', $webhookRequest);

        // assert
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertEquals(HttpClient::HTTP_METHOD_POST, $lastRequest['method']);
    }

    public function testRegisterWebhookBody(): void
    {
        // arrange
        $this->httpClient->setMockResponses([
            new HttpResponse(
                200,
                [],
                file_get_contents(__DIR__ . '/../../../../Common/ApiResponses/Webhook/registerWebhook.json')
            )
        ]);
        $webhookRequest = new WebhookRequest(
            'teststore.test',
            'username',
            'password'
        );

        // act
        $this->proxy->registerWebhook('1234', $webhookRequest);

        // assert
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertEquals(
            [
                'type' => 'standard',
                'url' => 'teststore.test',
                'username' => 'username',
                'password' => 'password',
                'active' => true,
                'communicationFormat' => 'json'
            ],
            json_decode($lastRequest['body'], true)
        );
    }

    public function testDeleteWebhookUrl(): void
    {
        // arrange
        $this->httpClient->setMockResponses(
            [
                new HttpResponse(204, [], '')
            ]
        );

        // act
        $this->proxy->deleteWebhook('1234', '012345678');

        // assert
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertEquals(
            'https://' . ProxyFactory::MANAGEMENT_API_TEST_URL . '/v1/merchants/1234/webhooks/012345678',
            $lastRequest['url']
        );
    }

    public function testDeleteWebhookMethod(): void
    {
        // arrange
        $this->httpClient->setMockResponses(
            [
                new HttpResponse(204, [], '')
            ]
        );

        // act
        $this->proxy->deleteWebhook('1234', '012345678');

        // assert
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertEquals(HttpClient::HTTP_METHOD_DELETE, $lastRequest['method']);
    }

    public function testGenerateHmacUrl(): void
    {
        // arrange
        $this->httpClient->setMockResponses([
            new HttpResponse(200, [], file_get_contents(__DIR__ . '/../../../../Common/ApiResponses/hmac.json'))
        ]);

        // act
        $this->proxy->generateHMAC('1234', '012345678');

        // assert
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertEquals(
            'https://' . ProxyFactory::MANAGEMENT_API_TEST_URL . '/v1/merchants/1234/webhooks/012345678/generateHmac',
            $lastRequest['url']
        );
    }

    public function testGenerateHmacMethod(): void
    {
        // arrange
        $this->httpClient->setMockResponses([
            new HttpResponse(200, [], file_get_contents(__DIR__ . '/../../../../Common/ApiResponses/hmac.json'))
        ]);

        // act
        $this->proxy->generateHMAC('1234', '012345678');

        // assert
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertEquals(HttpClient::HTTP_METHOD_POST, $lastRequest['method']);
    }

    /**
     * @throws HttpRequestException
     */
    public function testGetWebhookUrls(): void
    {
        // arrange
        $this->httpClient->setMockResponses([
            new HttpResponse(
                200,
                [],
                file_get_contents(__DIR__ . '/../../../../Common/ApiResponses/Webhook/webhooks.json')
            ),
            new HttpResponse(200, [],
                json_encode(
                    [
                        'itemsTotal' => 55,
                        'pagesTotal' => 0,
                        'data' => []
                    ]
                )
            )
        ]);

        // act
        $urls = $this->proxy->getWebhookURLs('1');

        // assert
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertEquals(HttpClient::HTTP_METHOD_GET, $lastRequest['method']);
        self::assertEquals($urls, $this->expectedUrls());
    }

    /**
     * @throws HttpRequestException
     */
    public function testGetWebhookConfig(): void
    {
        // arrange
        $this->httpClient->setMockResponses([
            new HttpResponse(
                200,
                [],
                file_get_contents(__DIR__ . '/../../../../Common/ApiResponses/Webhook/webhooks.json')
            ),
            new HttpResponse(200, [],
                json_encode(
                    [
                        'itemsTotal' => 55,
                        'pagesTotal' => 0,
                        'data' => []
                    ]
                )
            )
        ]);

        // act
        $config = $this->proxy->getWebhookConfigFromUrl('1', 'https://5-7-15.shopware5.sale.eu.ngrok.io/AdyenWebhook');

        // assert
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertEquals(HttpClient::HTTP_METHOD_GET, $lastRequest['method']);
        self::assertEquals($config, ['id' => 'S2-72453A3C7340', 'username' => 'LogeecomECOM', 'active' => true]);
    }

    /**
     * @throws HttpRequestException
     */
    public function testWebhookTestMethod(): void
    {
        // arrange
        $this->httpClient->setMockResponses([
            new HttpResponse(
                200,
                [],
                file_get_contents(__DIR__ . '/../../../../Common/ApiResponses/Webhook/testWebhook.json')
            ),
        ]);

        // act
        $this->proxy->testWebhook('1', '1');

        // assert
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertEquals(HttpClient::HTTP_METHOD_POST, $lastRequest['method']);
    }

    /**
     * @throws HttpRequestException
     */
    public function testWebhookTestUrl(): void
    {
        // arrange
        $this->httpClient->setMockResponses([
            new HttpResponse(
                200,
                [],
                file_get_contents(__DIR__ . '/../../../../Common/ApiResponses/Webhook/testWebhook.json')
            ),
        ]);

        // act
        $this->proxy->testWebhook('1', '1');

        // assert
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertEquals(
            'https://' . ProxyFactory::MANAGEMENT_API_TEST_URL . '/v1/merchants/1/webhooks/1/test',
            $lastRequest['url']
        );
    }

    /**
     * @throws HttpRequestException
     */
    public function testWebhookTestBody(): void
    {
        // arrange
        $this->httpClient->setMockResponses([
            new HttpResponse(
                200,
                [],
                file_get_contents(__DIR__ . '/../../../../Common/ApiResponses/Webhook/testWebhook.json')
            ),
        ]);

        // act
        $this->proxy->testWebhook('1', '1');

        // assert
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertEquals(
            $this->expectedTestBody(),
            $lastRequest['body']
        );
    }

    /**
     * @throws HttpRequestException
     */
    public function testWebhookTestSuccess(): void
    {
        // arrange
        $this->httpClient->setMockResponses([
            new HttpResponse(
                200,
                [],
                file_get_contents(__DIR__ . '/../../../../Common/ApiResponses/Webhook/testWebhook.json')
            ),
        ]);

        // act
        $test = $this->proxy->testWebhook('1', '1');

        // assert
        self::assertEquals($this->expectedTestResponse(), json_decode($test, true)['data'][0]);
    }

    /**
     * @throws HttpRequestException
     */
    public function testWebhookTestFail(): void
    {
        // arrange
        $this->httpClient->setMockResponses([
            new HttpResponse(
                200,
                [],
                file_get_contents(__DIR__ . '/../../../../Common/ApiResponses/Webhook/testWebhookFail.json')
            ),
        ]);

        // act
        $test = $this->proxy->testWebhook('1', '1');

        // assert
        self::assertEquals($this->expectedFailResponse(), json_decode($test, true));
    }

    public function testWebhookRetryFail(): void
    {
        // arrange
        $this->httpClient->setMockResponses([
            new HttpResponse(
                422,
                [],
                file_get_contents(__DIR__ . '/../../../../Common/ApiResponses/Webhook/testWebhookFail.json')
            ),
            new HttpResponse(
                200,
                [],
                file_get_contents(__DIR__ . '/../../../../Common/ApiResponses/Webhook/testWebhook.json')
            ),
        ]);

        // act
        $test = $this->proxy->testWebhook('1', '1');

        // assert
        self::assertEquals($this->expectedTestResponse(), json_decode($test, true)['data'][0]);
    }

    private function expectedFailResponse(): array
    {
        return [
            "type" => "https://docs.adyen.com/errors/not-found",
            "title" => "Entity was not found",
            "status" => 422,
            "requestId" => "D7T8TZFVD8JSTC82",
            "errorCode" => "30_112"
        ];
    }

    /**
     * @return string[]
     */
    private function expectedTestResponse(): array
    {
        return [
            "merchantId" => "Logeecom_LogeecomECOM1_TEST",
            "output" => "{\"notificationResponse\":\"[accepted]\"}",
            "requestSent" => "{\"live\":\"false\",\"notificationItems\":[{\"NotificationRequestItem\":{\"additionalData\":{\"expiryDate\":\"12\\/2012\",\"authCode\":\"1234\",\"cardSummary\":\"7777\",\"totalFraudScore\":\"10\",\"hmacSignature\":\"fO\\/59+3SXA0prTISYZR1S07fUM0gGB070LbO08WCx8s=\",\"NAME2\":\"VALUE2\",\"NAME1\":\"VALUE1\",\"fraudCheck-6-ShopperIpUsage\":\"10\"},\"amount\":{\"currency\":\"EUR\",\"value\":10100},\"eventCode\":\"AUTHORISATION\",\"eventDate\":\"2023-03-15T15:14:33+01:00\",\"merchantAccountCode\":\"Logeecom_LogeecomECOM1_TEST\",\"merchantReference\":\"8313842560770001\",\"operations\":[\"CANCEL\",\"CAPTURE\",\"REFUND\"],\"paymentMethod\":\"visa\",\"pspReference\":\"BLGXX6T5LFQ3FR8M\",\"reason\":\"1234:7777:12\\/2012\",\"success\":\"true\"}}]}",
            "responseCode" => "200",
            "responseTime" => "174 ms",
            "status" => "success"
        ];
    }

    /**
     * @return array
     */
    private function expectedUrls(): array
    {
        return
            [
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
     * @return string
     */
    private function expectedTestBody(): string
    {
        return "{\"notification\":{\"paymentMethod\":\"visa\",\"eventCode\":\"AUTHORISATION\",\"amount\":\"10\",\"reason\":\"Authorize visa payment\",\"success\":true},\"types\":[\"CUSTOM\",\"AUTHORISATION\"]}";
    }
}
