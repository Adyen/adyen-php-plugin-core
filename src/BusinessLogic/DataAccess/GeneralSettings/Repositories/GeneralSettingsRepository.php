<?php

namespace Adyen\Core\BusinessLogic\DataAccess\GeneralSettings\Repositories;

use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Models\GeneralSettings;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Repositories\GeneralSettingsRepository as BaseRepository;
use Adyen\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Adyen\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use Adyen\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use Adyen\Core\Infrastructure\ORM\QueryFilter\Operators;
use Adyen\Core\Infrastructure\ORM\QueryFilter\QueryFilter;
use Adyen\Core\BusinessLogic\DataAccess\GeneralSettings\Entities\GeneralSettings as GeneralSettingsEntity;

/**
 * Class GeneralSettingsRepository
 *
 * @package Adyen\Core\BusinessLogic\DataAccess\GeneralSettings\Repositories
 */
class GeneralSettingsRepository implements BaseRepository
{
    /**
     * @var RepositoryInterface
     */
    protected $repository;
    /**
     * @var StoreContext
     */
    protected $storeContext;

    /**
     * @param RepositoryInterface $repository
     * @param StoreContext $storeContext
     */
    public function __construct(RepositoryInterface $repository, StoreContext $storeContext)
    {
        $this->repository = $repository;
        $this->storeContext = $storeContext;
    }

    /**
     * @inheritDoc
     *
     * @throws QueryFilterInvalidParamException
     */
    public function getGeneralSettings(): ?GeneralSettings
    {
        $entity = $this->getGeneralSettingsEntity();

        return $entity ? $entity->getGeneralSettings() : null;
    }

    /**
     * @inheritDoc
     *
     * @throws QueryFilterInvalidParamException
     */
    public function setGeneralSettings(GeneralSettings $generalSettings): void
    {
        $existingGeneralSettings = $this->getGeneralSettingsEntity();

        if ($existingGeneralSettings) {
            $existingGeneralSettings->setGeneralSettings($generalSettings);
            $existingGeneralSettings->setStoreId($this->storeContext->getStoreId());
            $this->repository->update($existingGeneralSettings);

            return;
        }

        $entity = new GeneralSettingsEntity();
        $entity->setStoreId($this->storeContext->getStoreId());
        $entity->setGeneralSettings($generalSettings);
        $this->repository->save($entity);
    }

    /**
     * @inheritDoc
     */
    public function deleteGeneralSettings(): void
    {
        $settings = $this->getGeneralSettingsEntity();

        if (!$settings) {
            return;
        }

        $this->repository->delete($settings);
    }

    /**
     * @return GeneralSettingsEntity|null
     *
     * @throws QueryFilterInvalidParamException
     */
    protected function getGeneralSettingsEntity(): ?GeneralSettingsEntity
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId());

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->repository->selectOne($queryFilter);
    }
}
