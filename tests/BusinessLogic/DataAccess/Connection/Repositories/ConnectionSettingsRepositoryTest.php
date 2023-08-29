<?php

namespace Adyen\Core\Tests\BusinessLogic\DataAccess\Connection\Repositories;

use Adyen\Core\BusinessLogic\DataAccess\Connection\Entities\ConnectionSettings;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ApiCredentials;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionSettings as ConnectionSettingsModel;
use Adyen\Core\BusinessLogic\Domain\Connection\Repositories\ConnectionSettingsRepository;
use Adyen\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;
use Exception;

class ConnectionSettingsRepositoryTest extends BaseTestCase
{
    private $repository;

    private $connectionSettingsRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = TestRepositoryRegistry::getRepository(ConnectionSettings::getClassName());
        $this->connectionSettingsRepository = TestServiceRegister::getService(ConnectionSettingsRepository::class);
    }

    /**
     * @throws Exception
     */
    public function testGetSettingsNoSettings(): void
    {
        // act
        $result = StoreContext::doWithStore('1', [$this->connectionSettingsRepository, 'getConnectionSettings']);

        // assert
        self::assertNull($result);
    }

    /**
     * @throws Exception
     */
    public function testGetSettings(): void
    {
        // arrange
        $settings = new ConnectionSettingsModel(
            '1',
            'test',
            new ConnectionData('1234567890', '1111', '', '', new ApiCredentials('123', true, 'test')),
            null
        );
        $settingsEntity = new ConnectionSettings();
        $settingsEntity->setConnectionSettings($settings);
        $this->repository->save($settingsEntity);

        // act
        $result = StoreContext::doWithStore('1', [$this->connectionSettingsRepository, 'getOldestConnectionSettings']);

        // assert
        self::assertEquals($settings, $result);
    }

    /**
     * @throws Exception
     */
    public function testGetSettingsSetForDifferentStore(): void
    {
        // arrange
        $settings = new ConnectionSettingsModel(
            '1',
            'test',
            new ConnectionData('1234567890', '1111'),
            null
        );
        $settingsEntity = new ConnectionSettings();
        $settingsEntity->setConnectionSettings($settings);
        $this->repository->save($settingsEntity);

        // act
        $result = StoreContext::doWithStore('2', [$this->connectionSettingsRepository, 'getConnectionSettings']);

        // assert
        self::assertNull($result);
    }

    public function testSetSettings()
    {
        // arrange
        $settings = new ConnectionSettingsModel(
            '1',
            'test',
            new ConnectionData('1234567890', '1111', '', '', new ApiCredentials('123', true, 'test')),
            null
        );

        // act
        StoreContext::doWithStore('1', [$this->connectionSettingsRepository, 'setConnectionSettings'], [$settings]);

        // assert
        $savedEntity = $this->repository->select();
        self::assertEquals($settings, $savedEntity[0]->getConnectionSettings());
    }

    public function testSetSettingsAlreadyExists()
    {
        // arrange
        $settings = new ConnectionSettingsModel(
            '1',
            'test',
            new ConnectionData('1234567890', '1111'),
            null
        );
        $settingsEntity = new ConnectionSettings();
        $settingsEntity->setConnectionSettings($settings);
        $this->repository->save($settingsEntity);
        $newSettings = new ConnectionSettingsModel(
            '1',
            'test',
            new ConnectionData('0123456789', '2222', '', '', new ApiCredentials('123', true, 'test')),
            null
        );

        // act
        StoreContext::doWithStore('1', [$this->connectionSettingsRepository, 'setConnectionSettings'], [$newSettings]);

        // assert
        $savedEntity = $this->repository->selectOne();
        self::assertEquals($newSettings, $savedEntity->getConnectionSettings());
    }

    public function testSetSettingsAlreadyExistsForOtherStore()
    {
        // arrange
        $settings = new ConnectionSettingsModel(
            '1',
            'test',
            new ConnectionData('1234567890', '1111'),
            null
        );
        $settingsEntity = new ConnectionSettings();
        $settingsEntity->setConnectionSettings($settings);
        $this->repository->save($settingsEntity);
        $newSettings = new ConnectionSettingsModel(
            '2',
            'test',
            new ConnectionData('0123456789', '2222'),
            null
        );

        // act
        StoreContext::doWithStore('2', [$this->connectionSettingsRepository, 'setConnectionSettings'], [$newSettings]);

        // assert
        $savedEntity = $this->repository->select();
        self::assertCount(2, $savedEntity);
    }
}
