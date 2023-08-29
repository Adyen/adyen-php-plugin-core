<?php

namespace Adyen\Core\BusinessLogic\DataAccess\AdyenGivingSettings\Repositories;

use Adyen\Core\BusinessLogic\Domain\AdyenGivingSettings\Models\AdyenGivingSettings;
use Adyen\Core\BusinessLogic\Domain\AdyenGivingSettings\Repositories\AdyenGivingSettingsRepository as BaseRepository;
use Adyen\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Adyen\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use Adyen\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use Adyen\Core\Infrastructure\ORM\QueryFilter\Operators;
use Adyen\Core\Infrastructure\ORM\QueryFilter\QueryFilter;
use Adyen\Core\BusinessLogic\DataAccess\AdyenGivingSettings\Entities\AdyenGivingSettings as AdyenGivingSettingsEntity;


/**
 * Class AdyenGivingSettingsRepository
 *
 * @package Adyen\Core\BusinessLogic\DataAccess\AdyenGivingSettings\Repositories
 */
class AdyenGivingSettingsRepository implements BaseRepository
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
    public function getAdyenGivingSettings(): ?AdyenGivingSettings
    {
        $entity = $this->getAdyenGivingSettingsEntity();

        return $entity ? $entity->getAdyenGivingSettings() : null;
    }

    /**
     * @inheritDoc
     *
     * @throws QueryFilterInvalidParamException
     */
    public function setAdyenGivingSettings(AdyenGivingSettings $adyenGivingSettings): void
    {
        $existingAdyenGivingSettings = $this->getAdyenGivingSettingsEntity();

        if ($existingAdyenGivingSettings) {
            $existingAdyenGivingSettings->setAdyenGivingSettings($adyenGivingSettings);
            $existingAdyenGivingSettings->setStoreId($this->storeContext->getStoreId());
            $this->repository->update($existingAdyenGivingSettings);

            return;
        }

        $entity = new AdyenGivingSettingsEntity();
        $entity->setStoreId($this->storeContext->getStoreId());
        $entity->setAdyenGivingSettings($adyenGivingSettings);
        $this->repository->save($entity);
    }

    /**
     * @inheritDoc
     */
    public function deleteAdyenGivingSettings(): void
    {
        $settings = $this->getAdyenGivingSettingsEntity();

        if (!$settings) {
            return;
        }

        $this->repository->delete($settings);
    }

    /**
     * @return AdyenGivingSettingsEntity|null
     *
     * @throws QueryFilterInvalidParamException
     */
    protected function getAdyenGivingSettingsEntity(): ?AdyenGivingSettingsEntity
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId());

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->repository->selectOne($queryFilter);
    }
}
