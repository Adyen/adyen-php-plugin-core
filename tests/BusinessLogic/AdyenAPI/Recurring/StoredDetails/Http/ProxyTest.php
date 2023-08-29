<?php

namespace Adyen\Core\Tests\BusinessLogic\AdyenAPI\Recurring\StoredDetails\Http;

use Adyen\Core\BusinessLogic\AdyenAPI\Recurring\ProxyFactory;
use Adyen\Core\BusinessLogic\AdyenAPI\Recurring\StoredDetails\Http\Proxy;
use Adyen\Core\BusinessLogic\DataAccess\Connection\Entities\ConnectionSettings as ConnectionSettingsEntity;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\ShopperReference;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionSettings;
use Adyen\Core\BusinessLogic\Domain\Multistore\StoreContext;
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
        TestServiceRegister::registerService(HttpClient::class, static function () use ($httpClient) {
            return $httpClient;
        });

        $this->proxy = StoreContext::doWithStore('1', [$factory, 'makeProxy'], [Proxy::class]);
    }

    public function testDisableMethod(): void
    {
        // arrange
        $this->httpClient->setMockResponses([new HttpResponse(200, [], '')]);

        // act
        $this->proxy->disable(ShopperReference::parse('0123'), '4567', '1111');

        // assert
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertEquals(HttpClient::HTTP_METHOD_POST, $lastRequest['method']);
    }

    public function testDisableUrl(): void
    {
        // arrange
        $this->httpClient->setMockResponses([new HttpResponse(200, [], '')]);

        // act
        $this->proxy->disable(ShopperReference::parse('0123'), '4567', '1111');

        // assert
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertEquals(
            'https://' . ProxyFactory::RECURRING_API_TEST_URL . '/v68/disable',
            $lastRequest['url']
        );
    }

    public function testDisableBody(): void
    {
        // arrange
        $this->httpClient->setMockResponses([new HttpResponse(200, [], '')]);

        // act
        $this->proxy->disable(ShopperReference::parse('0123'), '4567', '1111');

        // assert
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertEquals(
            [
                'shopperReference' => '0123',
                'recurringDetailReference' => '4567',
                'merchantAccount' => '1111',
            ],
            json_decode($lastRequest['body'], true)
        );
    }
}
