<?php

namespace Adyen\Core\BusinessLogic\DataAccess\AdyenGiving\Repositories;

use Adyen\Core\BusinessLogic\DataAccess\AdyenGiving\Contracts\AdyenGivingRepository;
use Adyen\Core\BusinessLogic\DataAccess\AdyenGiving\Entities\DonationsData as DonationsDataEntity;
use Adyen\Core\BusinessLogic\Domain\Checkout\AdyenGiving\Models\DonationsData;
use Adyen\Core\BusinessLogic\Domain\Checkout\AdyenGiving\Repositories\DonationsDataRepository as BaseDonationsDataRepository;
use Adyen\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Adyen\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use Adyen\Core\Infrastructure\ORM\QueryFilter\Operators;
use Adyen\Core\Infrastructure\ORM\QueryFilter\QueryFilter;

/**
 * Class DonationsDataRepository
 *
 * @package Adyen\Core\BusinessLogic\DataAccess\AdyenGiving\Repositories
 */
class DonationsDataRepository implements BaseDonationsDataRepository
{
    /**
     * @var AdyenGivingRepository
     */
    protected $repository;

    /**
     * @var StoreContext
     */
    protected $storeContext;

    /**
     * @param AdyenGivingRepository $repository
     * @param StoreContext $storeContext
     */
    public function __construct(AdyenGivingRepository $repository, StoreContext $storeContext)
    {
        $this->repository = $repository;
        $this->storeContext = $storeContext;
    }

    /**
     * @inheritDoc
     */
    public function saveDonationsData(DonationsData $data): void
    {
        $entity = new DonationsDataEntity();
        $entity->setStoreId($this->storeContext->getStoreId());
        $entity->setMerchantReference($data->getMerchantReference());
        $entity->setDonationsData($data);

        $this->repository->save($entity);
    }

    /**
     * @inheritDoc
     */
    public function getDonationsData(string $merchantReference): ?DonationsData
    {
        $entity = $this->getEntity($merchantReference);

        return $entity ? $entity->getDonationsData() : null;
    }

    /**
     * @inheritDoc
     */
    public function deleteDonationsData(string $merchantReference): void
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId())
            ->where('merchantReference', Operators::EQUALS, $merchantReference);

        $this->repository->deleteWhere($queryFilter);
    }

    /**
     * @inheritDoc
     */
    public function delete(int $limit = 5000): void
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId());
        $queryFilter->setLimit($limit);

        $this->repository->deleteWhere($queryFilter);
    }

    /**
     * @inheritDoc
     */
    public function donationDataExists(): bool
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId());

        return $this->repository->count($queryFilter) > 0;
    }

    /**
     * @param string $merchantReference
     *
     * @return DonationsDataEntity|null
     * @throws QueryFilterInvalidParamException
     */
    private function getEntity(string $merchantReference): ?DonationsDataEntity
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId())
            ->where('merchantReference', Operators::EQUALS, $merchantReference);

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->repository->selectOne($queryFilter);
    }
}
