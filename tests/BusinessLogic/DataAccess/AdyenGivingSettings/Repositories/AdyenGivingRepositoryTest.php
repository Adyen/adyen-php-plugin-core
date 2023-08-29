<?php

namespace Adyen\Core\Tests\BusinessLogic\DataAccess\AdyenGivingSettings\Repositories;

use Adyen\Core\BusinessLogic\DataAccess\AdyenGivingSettings\Entities\AdyenGivingSettings as AdyenGivingSettingsEntity;
use Adyen\Core\BusinessLogic\Domain\AdyenGivingSettings\Models\AdyenGivingSettings as AdyenGivingSettingsModel;
use Adyen\Core\BusinessLogic\Domain\AdyenGivingSettings\Repositories\AdyenGivingSettingsRepository;
use Adyen\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Adyen\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use Adyen\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;
use Exception;


/**
 * Class AdyenGivingRepositoryTest
 *
 * @package Adyen\Core\Tests\BusinessLogic\DataAccess\AdyenGivingSettings\Repositories
 */
class AdyenGivingRepositoryTest extends BaseTestCase
{
    /**
     * @var RepositoryInterface
     */
    private $repository;

    /**
     * @var AdyenGivingSettingsRepository
     */
    private $adyenGivingSettingsRepository;

    /**
     * @throws RepositoryNotRegisteredException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = TestRepositoryRegistry::getRepository(AdyenGivingSettingsEntity::getClassName());
        $this->adyenGivingSettingsRepository = TestServiceRegister::getService(AdyenGivingSettingsRepository::class);
    }

    /**
     * @throws Exception
     */
    public function testGetSettingsNoSettings(): void
    {
        // act
        $result = StoreContext::doWithStore('1', [$this->adyenGivingSettingsRepository, 'getAdyenGivingSettings']);

        // assert
        self::assertNull($result);
    }

    /**
     * @throws Exception
     */
    public function testGetSettings(): void
    {
        // arrange
        $settings = new AdyenGivingSettingsModel(
            true,
            'name',
            'desc',
            'acc',
            [1, 2, 3],
            'website',
            'logo',
            'img'
        );
        $settingsEntity = new AdyenGivingSettingsEntity();
        $settingsEntity->setAdyenGivingSettings($settings);
        $settingsEntity->setStoreId('1');
        $this->repository->save($settingsEntity);

        // act
        $result = StoreContext::doWithStore('1', [$this->adyenGivingSettingsRepository, 'getAdyenGivingSettings']);

        // assert
        self::assertEquals($settings, $result);
    }

    /**
     * @throws Exception
     */
    public function testGetAdyenGivingSettingsSetForDifferentStore(): void
    {
        // arrange
        $settings = new AdyenGivingSettingsModel(
            true,
            'name',
            'desc',
            'acc',
            [1, 2, 3],
            'website',
            'logo',
            'img'
        );
        $settingsEntity = new AdyenGivingSettingsEntity();
        $settingsEntity->setAdyenGivingSettings($settings);
        $settingsEntity->setStoreId('1');
        $this->repository->save($settingsEntity);

        // act
        $result = StoreContext::doWithStore('2', [$this->adyenGivingSettingsRepository, 'getAdyenGivingSettings']);

        // assert
        self::assertNull($result);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testSetAdyenGivingSettings(): void
    {
        // arrange
        $settings = new AdyenGivingSettingsModel(
            true,
            'name',
            'desc',
            'acc',
            [1, 2, 3],
            'website',
            'logo',
            'img'
        );

        // act
        StoreContext::doWithStore('1', [$this->adyenGivingSettingsRepository, 'setAdyenGivingSettings'], [$settings]);

        // assert
        $savedEntity = $this->repository->select();
        self::assertEquals($settings, $savedEntity[0]->getAdyenGivingSettings());
    }

    /**
     * @throws Exception
     */
    public function testSetSettingsAlreadyExists(): void
    {
        // arrange
        $settings = new AdyenGivingSettingsModel(
            true,
            'name',
            'desc',
            'acc',
            [1, 2, 3],
            'website',
            'logo',
            'img'
        );
        $settingsEntity = new AdyenGivingSettingsEntity();
        $settingsEntity->setAdyenGivingSettings($settings);
        $settingsEntity->setStoreId('1');
        $this->repository->save($settingsEntity);
        $newSettings = new AdyenGivingSettingsModel(
            true,
            'newName',
            'newDesc',
            'newAcc',
            [4, 5, 6],
            'newWebsite',
            'logo',
            'img'
        );

        // act
        StoreContext::doWithStore('1', [$this->adyenGivingSettingsRepository, 'setAdyenGivingSettings'], [$newSettings]
        );

        // assert
        $savedEntity = $this->repository->selectOne();
        self::assertEquals($newSettings, $savedEntity->getAdyenGivingSettings());
    }

    /**
     * @throws Exception
     */
    public function testSetSettingsAlreadyExistsForOtherStore(): void
    {
        // arrange
        $settings = new AdyenGivingSettingsModel(
            true,
            'name',
            'desc',
            'acc',
            [1, 2, 3],
            'website',
            'logo',
            'img'
        );
        $settingsEntity = new AdyenGivingSettingsEntity();
        $settingsEntity->setAdyenGivingSettings($settings);
        $settingsEntity->setStoreId('1');
        $this->repository->save($settingsEntity);
        $settings2 = new AdyenGivingSettingsModel(
            true,
            'name2',
            'desc2',
            'acc2',
            [3, 4, 5],
            'website2',
            'logo2',
            'img2'
        );

        // act
        StoreContext::doWithStore('2', [$this->adyenGivingSettingsRepository, 'setAdyenGivingSettings'], [$settings2]);

        // assert
        $savedEntity = $this->repository->select();
        self::assertCount(2, $savedEntity);
        self::assertEquals($settings, $savedEntity[0]->getAdyenGivingSettings());
        self::assertEquals($settings2, $savedEntity[1]->getAdyenGivingSettings());
    }
}
