<?php

namespace Adyen\Core\Tests\BusinessLogic\AdminAPI\InfoSettings;

use Adyen\Core\BusinessLogic\AdminAPI\AdminAPI;
use Adyen\Core\BusinessLogic\DataAccess\Connection\Entities\ConnectionSettings;
use Adyen\Core\BusinessLogic\DataAccess\Payment\Entities\PaymentMethod;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use Adyen\Core\BusinessLogic\Domain\Integration\SystemInfo\SystemInfoService;
use Adyen\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use Adyen\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use Adyen\Core\Tests\BusinessLogic\AdminAPI\InfoSettings\Mocks\MockSystemInfoService;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\PaymentMethod as PaymentMethodModel;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionSettings as ConnectionSettingsModel;
use Exception;

/**
 * Class SystemInfoApiTest
 *
 * @package Adyen\Core\Tests\BusinessLogic\AdminAPI\InfoSettings
 */
class SystemInfoApiTest extends BaseTestCase
{
    /**
     * @var MockSystemInfoService
     */
    public $service;

    /**
     * @var RepositoryInterface
     */
    public $repository;

    /**
     * @var RepositoryInterface
     */
    public $connectionSettingsRepository;

    /**
     * @return void
     *
     * @throws RepositoryNotRegisteredException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new MockSystemInfoService();
        $this->repository = TestRepositoryRegistry::getRepository(PaymentMethod::getClassName());
        $this->connectionSettingsRepository = TestRepositoryRegistry::getRepository(ConnectionSettings::getClassName());

        TestServiceRegister::registerService(
            SystemInfoService::class,
            function () {
                return $this->service;
            }
        );
    }

    /**
     * @throws Exception
     */
    public function testGetSystemInfoSuccessful(): void
    {
        // Act
        $info = AdminAPI::get()->systemInfo()->getSystemInfo();

        // Assert
        self::assertTrue($info->isSuccessful());
    }

    /**
     * @throws Exception
     */
    public function testGetSystemInfoToArray(): void
    {
        // Act
        $info = AdminAPI::get()->systemInfo()->getSystemInfo();

        // Assert
        self::assertEquals($this->expectedToArray(), $info->toArray()['systemInfo']);
    }

    /**
     * @throws Exception
     */
    public function testPaymentMethods(): void
    {
        //arrange
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
        $pm2 = new PaymentMethodModel(
            '2345',
            'code',
            'name1',
            'logo1',
            true,
            ['USD'],
            ['US'],
            'type'

        );
        $entity2 = new PaymentMethod();
        $entity2->setStoreId('2');
        $entity2->setMethodId('2345');
        $entity2->setPaymentMethod($pm2);
        $entity2->setCode('code');
        $this->repository->save($entity2);

        // Act
        $info = AdminAPI::get()->systemInfo()->getSystemInfo();

        // Assert
        $methods = $info->toArray()['paymentMethods'];
        self::assertCount(2, $methods);
        self::assertEquals('1', $methods[0]['storeId']);
        self::assertEquals('2', $methods[1]['storeId']);
        self::assertEquals('name', $methods[0]['paymentMethod']['name']);
        self::assertEquals('name1', $methods[1]['paymentMethod']['name']);
    }

    /**
     * @throws Exception
     */
    public function testConnectionSettings(): void
    {
        //arrange
        $settings1 = new ConnectionSettingsModel(
            '1',
            'test',
            new ConnectionData('apiKey1', '1'),
            null
        );
        $entity1 = new ConnectionSettings();
        $entity1->setConnectionSettings($settings1);
        $this->repository->save($entity1);


        $settings2 = new ConnectionSettingsModel(
            '2',
            'test',
            new ConnectionData('apiKey2', '2'),
            null
        );
        $entity2 = new ConnectionSettings();
        $entity2->setConnectionSettings($settings2);
        $this->repository->save($entity2);

        // Act
        $info = AdminAPI::get()->systemInfo()->getSystemInfo();

        // Assert
        $settings = $info->toArray()['connectionSettings'];
        self::assertCount(2, $settings);
        self::assertEquals('1', $settings[0]['connectionSettings']['storeId']);
        self::assertEquals('2', $settings[1]['connectionSettings']['storeId']);
        self::assertEquals('***', $settings[0]['connectionSettings']['testData']['apiKey']);
        self::assertEquals('***', $settings[1]['connectionSettings']['testData']['apiKey']);
    }

    private function expectedToArray(): array
    {
        return [
            'systemVersion' => '1',
            'pluginVersion' => '1',
            'mainThemeName' => 'MAIN_THEME',
            'shopUrl' => 'shop.test',
            'adminUrl' => 'admin.test',
            'asyncProcessUrl' => 'async',
            'databaseName' => 'database',
            'databaseVersion' => 'databaseV'
        ];
    }
}
