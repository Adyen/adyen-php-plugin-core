<?php

namespace Adyen\Core\Tests\BusinessLogic\DataAccess\GeneralSettings\Repositories;

use Adyen\Core\BusinessLogic\DataAccess\GeneralSettings\Entities\GeneralSettings;
use Adyen\Core\BusinessLogic\DataAccess\GeneralSettings\Entities\GeneralSettings as GeneralSettingsEntity;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Exceptions\InvalidCaptureDelayException;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Exceptions\InvalidRetentionPeriodException;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Models\CaptureType as CaptureTypeModel;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Models\GeneralSettings as GeneralSettingsModel;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Repositories\GeneralSettingsRepository;
use Adyen\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Adyen\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use Adyen\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;
use Exception;

/**
 * Class GeneralSettingsRepository
 *
 * @package Adyen\Core\Tests\BusinessLogic\DataAccess\GeneralSettings\Repositories
 */
class GeneralSettingsRepositoryTest extends BaseTestCase
{
    /**
     * @var RepositoryInterface
     */
    private $repository;

    /**
     * @var GeneralSettingsRepository
     */
    private $generalSettingsRepository;

    /**
     * @throws RepositoryNotRegisteredException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = TestRepositoryRegistry::getRepository(GeneralSettingsEntity::getClassName());
        $this->generalSettingsRepository = TestServiceRegister::getService(GeneralSettingsRepository::class);
    }

    /**
     * @throws Exception
     */
    public function testGetSettingsNoSettings(): void
    {
        // act
        $result = StoreContext::doWithStore('1', [$this->generalSettingsRepository, 'getGeneralSettings']);

        // assert
        self::assertNull($result);
    }

    /**
     * @throws Exception
     */
    public function testGetSettings(): void
    {
        // arrange
        $settings = new GeneralSettingsModel(
            true,
            CaptureTypeModel::delayed(),
            1,
            's',
            60
        );
        $settingsEntity = new GeneralSettingsEntity();
        $settingsEntity->setGeneralSettings($settings);
        $settingsEntity->setStoreId('1');
        $this->repository->save($settingsEntity);

        // act
        $result = StoreContext::doWithStore('1', [$this->generalSettingsRepository, 'getGeneralSettings']);

        // assert
        self::assertEquals($settings, $result);
    }

    /**
     * @throws Exception
     */
    public function testGetGeneralSettingsSetForDifferentStore(): void
    {
        // arrange
        $settings = new GeneralSettingsModel(
            true,
            CaptureTypeModel::delayed(),
            1,
            's',
            60
        );
        $settingsEntity = new GeneralSettingsEntity();
        $settingsEntity->setGeneralSettings($settings);
        $settingsEntity->setStoreId('1');
        $this->repository->save($settingsEntity);

        // act
        $result = StoreContext::doWithStore('2', [$this->generalSettingsRepository, 'getGeneralSettings']);

        // assert
        self::assertNull($result);
    }

    /**
     * @return void
     *
     * @throws InvalidCaptureDelayException
     * @throws InvalidRetentionPeriodException
     * @throws Exception
     */
    public function testSetGeneralSettings(): void
    {
        // arrange
        $settings = new GeneralSettingsModel(
            true,
            CaptureTypeModel::delayed(),
            1,
            's',
            60
        );

        // act
        StoreContext::doWithStore('1', [$this->generalSettingsRepository, 'setGeneralSettings'], [$settings]);

        // assert
        $savedEntity = $this->repository->select();
        self::assertEquals($settings, $savedEntity[0]->getGeneralSettings());
    }


    /**
     * @throws InvalidCaptureDelayException
     * @throws InvalidRetentionPeriodException
     *
     * @throws Exception
     */
    public function testSetSettingsAlreadyExists(): void
    {
        // arrange
        $settings = new GeneralSettingsModel(
            true,
            CaptureTypeModel::delayed(),
            1,
            's',
            60
        );
        $settingsEntity = new GeneralSettingsEntity();
        $settingsEntity->setGeneralSettings($settings);
        $settingsEntity->setStoreId('1');
        $this->repository->save($settingsEntity);
        $newSettings = new GeneralSettingsModel(
            false,
            CaptureTypeModel::immediate(),
            2,
            'sa',
            62
        );

        // act
        StoreContext::doWithStore('1', [$this->generalSettingsRepository, 'setGeneralSettings'], [$newSettings]);

        // assert
        $savedEntity = $this->repository->selectOne();
        self::assertEquals($newSettings, $savedEntity->getGeneralSettings());
    }

    /**
     * @throws InvalidCaptureDelayException
     * @throws InvalidRetentionPeriodException
     * @throws Exception
     */
    public function testSetSettingsAlreadyExistsForOtherStore(): void
    {
        // arrange
        $settings = new GeneralSettingsModel(
            true,
            CaptureTypeModel::delayed(),
            1,
            's',
            60
        );
        $settingsEntity = new GeneralSettings();
        $settingsEntity->setGeneralSettings($settings);
        $settingsEntity->setStoreId('1');
        $this->repository->save($settingsEntity);
        $newSettings = new GeneralSettingsModel(
            false,
            CaptureTypeModel::immediate(),
            2,
            'sa',
            62
        );

        // act
        StoreContext::doWithStore('2', [$this->generalSettingsRepository, 'setGeneralSettings'], [$newSettings]);

        // assert
        $savedEntity = $this->repository->select();
        self::assertCount(2, $savedEntity);
        self::assertEquals($settings, $savedEntity[0]->getGeneralSettings());
        self::assertEquals($newSettings, $savedEntity[1]->getGeneralSettings());
    }
}
