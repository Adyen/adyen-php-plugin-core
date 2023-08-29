<?php

namespace Adyen\Core\Tests\BusinessLogic\AdminAPI\Merchant;

use Adyen\Core\BusinessLogic\AdminAPI\AdminAPI;
use Adyen\Core\BusinessLogic\AdminAPI\Merchant\Controller\MerchantController;
use Adyen\Core\BusinessLogic\AdminAPI\Merchant\Response\MerchantResponse;
use Adyen\Core\BusinessLogic\Bootstrap\SingleInstance;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ApiCredentials;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionSettings;
use Adyen\Core\BusinessLogic\Domain\Connection\Repositories\ConnectionSettingsRepository;
use Adyen\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use Adyen\Core\BusinessLogic\Domain\Merchant\Models\Merchant;
use Adyen\Core\BusinessLogic\Domain\Merchant\Proxies\MerchantProxy;
use Adyen\Core\BusinessLogic\Domain\Merchant\Services\MerchantService;
use Adyen\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Adyen\Core\Tests\BusinessLogic\AdyenAPI\MockComponents\MockMerchantProxy;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;
use Exception;

class MerchantApiTest extends BaseTestCase
{
    /**
     * @var MockMerchantProxy
     */
    private $merchantProxy;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->merchantProxy = new MockMerchantProxy();
        /** @var ConnectionSettingsRepository $repository */
        $repository = TestServiceRegister::getService(ConnectionSettingsRepository::class);

        StoreContext::doWithStore(
            'store1',
            [$repository, 'setConnectionSettings'],
            [new ConnectionSettings(
                'store1',
                'test',
                new ConnectionData('1234', '', '', '', new ApiCredentials('123', true, 'Logeecom')),
                null)
            ]
        );

        TestServiceRegister::registerService(
            MerchantService::class,
            new SingleInstance(function () {
                return new  MerchantService(
                    $this->merchantProxy,
                    TestServiceRegister::getService(ConnectionService::class)
                );
            })
        );

        TestServiceRegister::registerService(
            MerchantProxy::class,
            new SingleInstance(function () {
                return $this->merchantProxy;
            })
        );

        TestServiceRegister::registerService(
            MerchantController::class,
            new SingleInstance(function () {
                return new MerchantController(TestServiceRegister::getService(MerchantService::class));
            })
        );
    }

    /**
     * @throws Exception
     */
    public function testIsResponseSuccessful(): void
    {
        // Arrange
        $this->merchantProxy->setMockResult([
            new Merchant('LogeecomECOM', 'LogeecomECOM', '', 'Logeecom'),
        ]);

        // Act
        $merchants = AdminAPI::get()->merchant('store1')->getMerchants();

        // Assert
        self::assertTrue($merchants->isSuccessful());
    }

    /**
     * @throws Exception
     */
    public function testMerchantResponse(): void
    {
        // Arrange
        $this->merchantProxy->setMockResult([
            new Merchant('LogeecomECOM', 'LogeecomECOM', '', 'Logeecom'),
            new Merchant('Logeecom_LogeecomECOM1_TEST', 'Logeecom_LogeecomECOM1_TEST', '', 'Logeecom'),
            new Merchant('Logeecom_Sava_local_test_TEST', 'Logeecom_Sava_local_test_TEST', '', 'Logeecom'),
        ]);

        // Act
        $merchants = AdminAPI::get()->merchant('store1')->getMerchants();

        // Assert
        self::assertEquals($merchants, $this->expectedResponse());
    }

    /**
     * @throws Exception
     */
    public function testMerchantResponseToArray(): void
    {
        // Arrange
        $this->merchantProxy->setMockResult([
            new Merchant('LogeecomECOM', 'LogeecomECOM', '', 'Logeecom'),
            new Merchant('Logeecom_LogeecomECOM1_TEST', 'Logeecom_LogeecomECOM1_TEST', '', 'Logeecom'),
            new Merchant('Logeecom_Sava_local_test_TEST', 'Logeecom_Sava_local_test_TEST', '', 'Logeecom'),
        ]);

        // Act
        $merchants = AdminAPI::get()->merchant('store1')->getMerchants();

        // Assert
        self::assertEquals($merchants->toArray(), $this->expectedResponseToArray());
    }

    private function expectedResponse(): MerchantResponse
    {
        return new MerchantResponse([
            new Merchant('LogeecomECOM', 'LogeecomECOM', '', 'Logeecom'),
            new Merchant('Logeecom_LogeecomECOM1_TEST', 'Logeecom_LogeecomECOM1_TEST', '', 'Logeecom'),
            new Merchant('Logeecom_Sava_local_test_TEST', 'Logeecom_Sava_local_test_TEST', '', 'Logeecom'),
        ]);
    }

    private function expectedResponseToArray(): array
    {
        return [
            0 => ['merchantName' => 'LogeecomECOM', 'merchantId' => 'LogeecomECOM'],
            1 => [
                'merchantName' => 'Logeecom_LogeecomECOM1_TEST',
                'merchantId' => 'Logeecom_LogeecomECOM1_TEST'
            ],
            2 => [
                'merchantName' => 'Logeecom_Sava_local_test_TEST',
                'merchantId' => 'Logeecom_Sava_local_test_TEST'
            ]
        ];
    }
}
