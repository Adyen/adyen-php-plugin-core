<?php

namespace Adyen\Core\BusinessLogic\DataAccess\Connection\Repositories;

use Adyen\Core\BusinessLogic\DataAccess\Connection\Entities\ConnectionSettings as ConnectionSettingsEntity;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionSettings;
use Adyen\Core\BusinessLogic\Domain\Connection\Repositories\ConnectionSettingsRepository as BaseRepository;
use Adyen\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Adyen\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use Adyen\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use Adyen\Core\Infrastructure\ORM\QueryFilter\Operators;
use Adyen\Core\Infrastructure\ORM\QueryFilter\QueryFilter;

/**
 * Class ConnectionSettingsRepository
 *
 * @package Adyen\Core\BusinessLogic\DataAccess\Connection\Repositories
 */
class ConnectionSettingsRepository implements BaseRepository
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
    public function getConnectionSettings(): ?ConnectionSettings
    {
        $entity = $this->getConnectionSettingsEntity();

        return $entity ? $entity->getConnectionSettings() : null;
    }

    /**
     * @inheritDoc
     *
     * @throws QueryFilterInvalidParamException
     */
    public function setConnectionSettings(ConnectionSettings $connectionSettings): void
    {
        $existingSettings = $this->getConnectionSettingsEntity();

        if ($existingSettings) {
            $existingSettings->setConnectionSettings($connectionSettings);
            $this->repository->update($existingSettings);

            return;
        }

        $entity = new ConnectionSettingsEntity();
        $entity->setConnectionSettings($connectionSettings);
        $this->repository->save($entity);
    }

    /**
     * @inheritDoc
     */
    public function getOldestConnectionSettings(): ?ConnectionSettings
    {
        /** @var ConnectionSettingsEntity $item */
        $item = $this->repository->selectOne(new QueryFilter());

        return $item ? $item->getConnectionSettings() : null;
    }

    /**
     * @inheritDoc
     */
    public function getActiveConnectionData(): ?ConnectionData
    {
        $settings = $this->getConnectionSettings();
        if (!$settings) {
            return null;
        }

        return $settings->getActiveConnectionData();
    }

    /**
     * @inheritDoc
     *
     * @throws QueryFilterInvalidParamException
     */
    public function deleteConnectionSettings(): void
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId());

        $this->repository->deleteWhere($queryFilter);
    }

    public function getAllConnectionSettings(): array
    {
        $entities = $this->repository->select();

        return array_map(static function (ConnectionSettingsEntity $entity) {
            return $entity->getConnectionSettings();
        }, $entities);
    }

    /**
     * @return ConnectionSettingsEntity|null
     *
     * @throws QueryFilterInvalidParamException
     */
    protected function getConnectionSettingsEntity(): ?ConnectionSettingsEntity
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId());

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->repository->selectOne($queryFilter);
    }
}
