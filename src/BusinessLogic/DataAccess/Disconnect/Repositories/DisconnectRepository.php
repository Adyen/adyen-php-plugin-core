<?php

namespace Adyen\Core\BusinessLogic\DataAccess\Disconnect\Repositories;

use Adyen\Core\BusinessLogic\DataAccess\Disconnect\Entities\DisconnectTime;
use Adyen\Core\BusinessLogic\Domain\Disconnect\Repositories\DisconnectRepository as BaseRepository;
use Adyen\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Adyen\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use Adyen\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use Adyen\Core\Infrastructure\ORM\QueryFilter\Operators;
use Adyen\Core\Infrastructure\ORM\QueryFilter\QueryFilter;
use DateTime;

/**
 * Class DisconnectRepository
 *
 * @package Adyen\Core\BusinessLogic\DataAccess\Disconnect\Repositories
 */
class DisconnectRepository implements BaseRepository
{
    /**
     * @var StoreContext
     */
    protected $storeContext;
    /**
     * @var RepositoryInterface
     */
    protected $repository;

    /**
     * @param StoreContext $storeContext
     * @param RepositoryInterface $repository
     */
    public function __construct(StoreContext $storeContext, RepositoryInterface $repository)
    {
        $this->storeContext = $storeContext;
        $this->repository = $repository;
    }

    /**
     * @inheritDoc
     */
    public function getDisconnectTime(): ?DateTime
    {
        $entity = $this->getDisconnectTimeEntity();

        return $entity ? $entity->getDate() : null;
    }

    /**
     * @inheritDoc
     */
    public function setDisconnectTime(DateTime $disconnectTime): void
    {
        $existingDisconnectTime = $this->getDisconnectTimeEntity();

        if ($existingDisconnectTime) {
            $existingDisconnectTime->setDate($disconnectTime);
            $existingDisconnectTime->setStoreId($this->storeContext->getStoreId());
            $this->repository->update($existingDisconnectTime);

            return;
        }

        $entity = new DisconnectTime();
        $entity->setStoreId($this->storeContext->getStoreId());
        $entity->setDate($disconnectTime);
        $this->repository->save($entity);
    }

    /**
     * @inheritDoc
     */
    public function deleteDisconnectTime(): void
    {
        $disconnectTime = $this->getDisconnectTimeEntity();

        if (!$disconnectTime) {
            return;
        }

        $this->repository->delete($disconnectTime);
    }

    /**
     * @return DisconnectTime|null
     *
     * @throws QueryFilterInvalidParamException
     */
    protected function getDisconnectTimeEntity(): ?DisconnectTime
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId());

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->repository->selectOne($queryFilter);
    }
}
