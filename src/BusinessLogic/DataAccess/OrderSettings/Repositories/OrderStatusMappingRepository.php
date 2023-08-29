<?php

namespace Adyen\Core\BusinessLogic\DataAccess\OrderSettings\Repositories;

use Adyen\Core\BusinessLogic\DataAccess\OrderSettings\Entities\OrderStatusMapping as OrderStatusMappingSettingsEntity;
use Adyen\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Adyen\Core\BusinessLogic\Webhook\Repositories\OrderStatusMappingRepository as BaseRepository;
use Adyen\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use Adyen\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use Adyen\Core\Infrastructure\ORM\QueryFilter\Operators;
use Adyen\Core\Infrastructure\ORM\QueryFilter\QueryFilter;

/**
 * Class OrderStatusMappingRepository
 *
 * @package Adyen\Core\BusinessLogic\DataAccess\OrderSettings\Repositories
 */
class OrderStatusMappingRepository implements BaseRepository
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
    public function getOrderStatusMapping(): array
    {
        $entity = $this->getOrderStatusMappingsEntity();

        return $entity ? $entity->getOrderStatusMappingSettings() : [];
    }

    /**
     * @inheritDoc
     *
     * @throws QueryFilterInvalidParamException
     */
    public function setOrderStatusMapping(array $orderStatusMapping): void
    {
        $existingOrderStatusMapping = $this->getOrderStatusMappingsEntity();

        if ($existingOrderStatusMapping) {
            $existingOrderStatusMapping->setOrderStatusMappingSettings($orderStatusMapping);
            $existingOrderStatusMapping->setStoreId($this->storeContext->getStoreId());
            $this->repository->update($existingOrderStatusMapping);

            return;
        }

        $entity = new OrderStatusMappingSettingsEntity();
        $entity->setStoreId($this->storeContext->getStoreId());
        $entity->setOrderStatusMappingSettings($orderStatusMapping);
        $this->repository->save($entity);
    }

    public function deleteOrderStatusMapping(): void
    {
        $mappings = $this->getOrderStatusMappingsEntity();

        if (!$mappings) {
            return;
        }

        $this->repository->delete($mappings);
    }

    /**
     * @return OrderStatusMappingSettingsEntity|null
     *
     * @throws QueryFilterInvalidParamException
     */
    protected function getOrderStatusMappingsEntity(): ?OrderStatusMappingSettingsEntity
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId());

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->repository->selectOne($queryFilter);
    }
}
