<?php

namespace Adyen\Core\Tests\BusinessLogic\AdminAPI\Payment;

use Adyen\Core\BusinessLogic\AdminAPI\Payment\Controller\PaymentController;
use Adyen\Core\BusinessLogic\AdminAPI\Payment\Request\PaymentMethodRequest;
use Adyen\Core\BusinessLogic\AdminAPI\Payment\Response\AvailableMethodsResponse;
use Adyen\Core\BusinessLogic\AdminAPI\Payment\Response\PaymentMethodResponse;
use Adyen\Core\BusinessLogic\AdminAPI\Payment\Response\PaymentResponse;
use Adyen\Core\BusinessLogic\DataAccess\Connection\Entities\ConnectionSettings as ConnectionSettingsEntity;
use Adyen\Core\BusinessLogic\DataAccess\Payment\Entities\PaymentMethod;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Proxies\PaymentsProxy;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionSettings;
use Adyen\Core\BusinessLogic\Domain\Connection\Repositories\ConnectionSettingsRepository;
use Adyen\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\MethodAdditionalData\ApplePay;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\MethodAdditionalData\CardConfig;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\PaymentMethod as PaymentMethodModel;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\PaymentMethodResponse as PaymentMethodResponseModel;
use Adyen\Core\BusinessLogic\Domain\Payment\Proxies\PaymentProxy;
use Adyen\Core\BusinessLogic\Domain\Payment\Repositories\PaymentMethodConfigRepository;
use Adyen\Core\BusinessLogic\Domain\Payment\Services\PaymentService;
use Adyen\Core\Infrastructure\Http\HttpClient;
use Adyen\Core\Infrastructure\Http\HttpResponse;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Adyen\Core\Tests\BusinessLogic\Domain\Payment\MockPaymentService;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\TestHttpClient;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;
use Exception;

class PaymentControllerTest extends BaseTestCase
{
    public $repository;
    public $service;
    public $controller;
    public $httpClient;

    protected function setUp(): void
    {
        parent::setUp();

        $repository = TestRepositoryRegistry::getRepository(ConnectionSettingsEntity::getClassName());
        $settings = new ConnectionSettings(
            '1',
            'test',
            new ConnectionData('1234567890', '1111'),
            null
        );
        $settingsEntity = new ConnectionSettingsEntity();
        $settingsEntity->setConnectionSettings($settings);
        $repository->save($settingsEntity);

        $httpClient = new TestHttpClient();
        $this->httpClient = $httpClient;
        TestServiceRegister::registerService(HttpClient::class, function () use ($httpClient) {
            return $httpClient;
        });

        $service = new MockPaymentService(
            TestServiceRegister::getService(PaymentMethodConfigRepository::class),
            TestServiceRegister::getService(ConnectionSettingsRepository::class),
            TestServiceRegister::getService(PaymentProxy::class),
            TestServiceRegister::getService(PaymentsProxy::class)
        );
        $this->service = $service;
        TestServiceRegister::registerService(
            PaymentService::class,
            static function () use ($service) {
                return $service;
            }
        );
        $this->repository = TestRepositoryRegistry::getRepository(PaymentMethod::getClassName());
        $this->controller = TestServiceRegister::getService(PaymentController::class);
    }

    public function testGetConfiguredPaymentMethods(): void
    {
        // arrange
        $this->httpClient->setMockResponses(
            [
                new HttpResponse(
                    200,
                    [],
                    file_get_contents(__DIR__ . '/../../Common/ApiResponses/PaymentAdminAPI/managementMethods.json')
                ),
                new HttpResponse(200, [], json_encode(
                    [
                        'itemsTotal' => 47,
                        'pagesTotal' => 0,
                        'data' => []
                    ]
                ))
            ]
        );
        $pm1 = new PaymentMethodModel(
            'PM3224P22322225FNZ7B8G595',
            'scheme',
            'Credit Card',
            'https://checkoutshopper-live.adyen.com/checkoutshopper/images/logos/card.svg',
            true,
            ['ANY'],
            ['ANY'],
            'creditOrDebitCard'
        );
        $pm1->setPercentSurcharge(0.0);
        $pm1->setAdditionalData(
            new CardConfig(
                false, false, false, false, false, [], 0.0, []
            )
        );
        $entity1 = new PaymentMethod();
        $entity1->setStoreId('1');
        $entity1->setMethodId('PM3224P22322225FNZ7B8G595');
        $entity1->setCode('scheme');
        $entity1->setPaymentMethod($pm1);
        $this->repository->save($entity1);
        $pm2 = new PaymentMethodModel(
            'PM3224R223224K5FRJX5ZDCZL',
            'applepay',
            'Apple Pay',
            'https://checkoutshopper-live.adyen.com/checkoutshopper/images/logos/applepay.svg',
            true,
            ['ANY'],
            ['ANY'],
            'wallet'

        );
        $pm2->setPercentSurcharge(0.0);
        $pm2->setAdditionalData(
            new ApplePay(
                '', '', false
            )
        );
        $entity2 = new PaymentMethod();
        $entity2->setStoreId('1');
        $entity2->setMethodId('PM3224R223224K5FRJX5ZDCZL');
        $entity2->setPaymentMethod($pm2);
        $entity2->setCode('applepay');
        $this->repository->save($entity2);

        // act
        $result = StoreContext::doWithStore('1', [$this->controller, 'getConfiguredPaymentMethods']);

        // assert
        self::assertEquals(new PaymentResponse([$pm1, $pm2]), $result);
    }

    public function testGetConfiguredPaymentMethodsFails(): void
    {
        // arrange
        $this->service->getConfiguredPaymentMethodsFails = true;
        $this->expectException(Exception::class);

        // act
        $result = StoreContext::doWithStore('1', [$this->controller, 'getConfiguredPaymentMethods']);
    }

    public function testGetMethodById(): void
    {
        // arrange
        $pm1 = new PaymentMethodModel(
            '1234',
            'code',
            'name',
            'logo',
            true,
            ['EUR'],
            ['FR'],
            'type'
        );
        $entity1 = new PaymentMethod();
        $entity1->setStoreId('1');
        $entity1->setMethodId('1234');
        $entity1->setCode('code');
        $entity1->setPaymentMethod($pm1);
        $this->repository->save($entity1);

        // act
        $result = StoreContext::doWithStore('1', [$this->controller, 'getMethodById'], ['1234']);

        // assert
        self::assertEquals(new PaymentMethodResponse($pm1), $result);
    }

    public function testGetAvailablePaymentMethodsNoMethodsOnCheckoutAPI(): void
    {
        // arrange
        $this->httpClient->setMockResponses(
            [
                new HttpResponse(200, [], ''),
                new HttpResponse(
                    200,
                    [],
                    file_get_contents(__DIR__ . '/../../Common/ApiResponses/PaymentAdminAPI/managementMethods.json')
                ),
                new HttpResponse(200, [], json_encode(
                    [
                        'itemsTotal' => 47,
                        'pagesTotal' => 0,
                        'data' => []
                    ]
                )),
            ]
        );

        // act
        $result = StoreContext::doWithStore('1', [$this->controller, 'getAvailablePaymentMethods']);

        // assert
        self::assertEquals(new AvailableMethodsResponse([]), $result);
    }

    public function testGetAvailablePaymentMethodsNoMethodsOnManagementAPI(): void
    {
        // arrange
        $this->httpClient->setMockResponses(
            [
                new HttpResponse(
                    200,
                    [],
                    file_get_contents(__DIR__ . '/../../Common/ApiResponses/PaymentAdminAPI/checkoutMethods.json')
                ),
                new HttpResponse(200, [], ''),
            ]
        );

        // act
        $result = StoreContext::doWithStore('1', [$this->controller, 'getAvailablePaymentMethods']);

        // assert
        self::assertEquals(new AvailableMethodsResponse([]), $result);
    }

    public function testGetAvailablePaymentMethods(): void
    {
        // arrange
        $this->httpClient->setMockResponses(
            [
                new HttpResponse(
                    200,
                    [],
                    file_get_contents(__DIR__ . '/../../Common/ApiResponses/PaymentAdminAPI/checkoutMethods.json')
                ),
                new HttpResponse(
                    200,
                    [],
                    file_get_contents(__DIR__ . '/../../Common/ApiResponses/PaymentAdminAPI/managementMethods.json')
                ),
                new HttpResponse(200, [], json_encode(
                    [
                        'itemsTotal' => 47,
                        'pagesTotal' => 0,
                        'data' => []
                    ]
                )),
            ]
        );
        $pm1 = new PaymentMethodModel(
            'PM3224R223224K5FRJX5ZDCZL',
            'applepay',
            'Apple Pay',
            'logo',
            true,
            ['EUR'],
            ['FR'],
            'type'
        );
        $entity1 = new PaymentMethod();
        $entity1->setStoreId('1');
        $entity1->setMethodId('PM3224R223224K5FRJX5ZDCZL');
        $entity1->setCode('code');
        $entity1->setPaymentMethod($pm1);
        $this->repository->save($entity1);
        $availablePaymentMethods = [
            new PaymentMethodResponseModel('PM3224P22322225FR8FHP6DQF', 'alipay', true, ['ANY'],
                [
                    "CHF",
                    "HKD",
                    "EUR",
                    "DKK",
                    "USD",
                    "MYR",
                    "CAD",
                    "NOK",
                    "CNY",
                    "THB",
                    "AUD",
                    "SGD",
                    "JPY",
                    "GBP",
                    "CZK",
                    "SEK",
                    "NZD",
                    "RUB"
                ], 'https://checkoutshopper-live.adyen.com/checkoutshopper/images/logos/alipay.svg',
                'wallet', 'AliPay'),
            new PaymentMethodResponseModel('PM3224P22322225FNZ7B8G595', 'scheme', true, ['ANY'], ['ANY'],
                'https://checkoutshopper-live.adyen.com/checkoutshopper/images/logos/card.svg',
                'creditOrDebitCard', 'Credit Card'
            ),
        ];

        // act
        $result = StoreContext::doWithStore('1', [$this->controller, 'getAvailablePaymentMethods']);

        // assert
        self::assertEquals(new AvailableMethodsResponse($availablePaymentMethods), $result);
    }

    public function testSaveMethodConfiguration(): void
    {
        // arrange
        $configuration = new PaymentMethodModel(
            '1234',
            'code',
            'name',
            'logo',
            true,
            [],
            [],
            'type',
            'description'
        );
        $request = PaymentMethodRequest::parse(
            [
                'methodId' => '1234',
                'logo' => 'logo',
                'name' => 'name',
                'status' => true,
                'currencies' => [],
                'countries' => [],
                'type' => 'type',
                'description' => 'description',
                'code' => 'code'
            ]
        );

        // act
        StoreContext::doWithStore('1', [$this->controller, 'saveMethodConfiguration'], [$request]);


        // assert
        $savedData = $this->repository->selectOne();
        self::assertEquals($configuration, $savedData->getPaymentMethod());
    }

    public function testUpdateMethodConfiguration(): void
    {
        // arrange
        $configuration = new PaymentMethodModel(
            '1234',
            'code',
            'name',
            'logo',
            true,
            [],
            [],
            'type',
            'description'
        );
        $entity = new PaymentMethod();
        $entity->setPaymentMethod($configuration);
        $entity->setMethodId('1234');
        $entity->setStoreId('1');
        $entity->setCode('code');
        $this->repository->save($entity);
        $newConfig = new PaymentMethodModel(
            '1234',
            'code',
            'name1',
            'logo',
            true,
            [],
            [],
            'type1',
            'description2'
        );
        $request = PaymentMethodRequest::parse(
            [
                'methodId' => '1234',
                'logo' => 'logo',
                'name' => 'name1',
                'status' => true,
                'currencies' => [],
                'countries' => [],
                'code' => 'code',
                'type' => 'type1',
                'description' => 'description2',
            ]
        );

        // act
        StoreContext::doWithStore('1', [$this->controller, 'updateMethodConfiguration'], [$request]);

        // assert
        $result = $this->repository->selectOne();
        self::assertEquals($newConfig, $result->getPaymentMethod());
    }

    public function testUpdateMethodConfigurationMethodNotConfigured(): void
    {
        // arrange
        $this->expectException(Exception::class);
        $configuration = new PaymentMethodModel(
            '1234',
            'code',
            'name',
            'code',
            true,
            [],
            [],
            'type',
            'description'
        );
        $request = PaymentMethodRequest::parse(
            [
                'methodId' => '1234',
                'logo' => 'logo',
                'name' => 'name',
                'status' => true,
                'currencies' => [],
                'countries' => [],
                'type' => 'type',
                'description' => 'description',
            ]
        );

        // act
        StoreContext::doWithStore('1', [$this->controller, 'updateMethodConfiguration'], [$request]);
    }
}

