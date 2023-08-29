<?php

namespace Adyen\Core\BusinessLogic\DataAccess\TransactionLog\Repositories;

use Adyen\Core\BusinessLogic\DataAccess\TransactionLog\Contracts\ShopLogsRepository;
use Adyen\Core\BusinessLogic\DataAccess\TransactionLog\Entities\TransactionLog;
use Adyen\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Adyen\Core\BusinessLogic\TransactionLog\Repositories\TransactionLogRepository as TransactionLogRepositoryInterface;
use Adyen\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use Adyen\Core\Infrastructure\ORM\QueryFilter\Operators;
use Adyen\Core\Infrastructure\ORM\QueryFilter\QueryFilter;
use DateTime;

/**
 * Class TransactionLogRepository
 *
 * @package Adyen\Core\BusinessLogic\DataAccess\TransactionLog\Repositories
 */
class TransactionLogRepository implements TransactionLogRepositoryInterface
{
    /**
     * @var ShopLogsRepository
     */
    protected $repository;

    /**
     * @var StoreContext
     */
    protected $storeContext;

    /**
     * @param ShopLogsRepository $repository
     * @param StoreContext $storeContext
     */
    public function __construct(ShopLogsRepository $repository, StoreContext $storeContext)
    {
        $this->repository = $repository;
        $this->storeContext = $storeContext;
    }

    /**
     * @inheritDoc
     *
     * @throws QueryFilterInvalidParamException
     */
    public function getTransactionLog(string $merchantReference): ?TransactionLog
    {
        $entity = $this->getTransactionLogEntity($merchantReference);

        return $entity ?: null;
    }

    /**
     * @inheritDoc
     */
    public function setTransactionLog(TransactionLog $transactionLog): void
    {
        $this->repository->save($transactionLog);
    }

    /**
     * @param TransactionLog $transactionLog
     *
     * @return void
     */
    public function updateTransactionLog(TransactionLog $transactionLog): void
    {
        $this->repository->update($transactionLog);
    }

    /**
     * @param int $limit
     * @param int $offset
     * @param DateTime|null $disconnectTime
     *
     * @return TransactionLog[]
     *
     * @throws QueryFilterInvalidParamException
     */
    public function find(int $limit, int $offset, ?DateTime $disconnectTime = null): array
    {
        $queryFilter = new QueryFilter();
        $queryFilter->setLimit($limit);
        $queryFilter->setOffset($offset);
        $queryFilter->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId())->orderBy(
            'id',
            QueryFilter::ORDER_DESC
        );

        if ($disconnectTime) {
            $queryFilter->where('timestamp', Operators::GREATER_THAN, $disconnectTime);
        }

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->repository->select($queryFilter);
    }

    /**
     * @param int $executionId
     *
     * @return ?TransactionLog
     *
     * @throws QueryFilterInvalidParamException
     */
    public function getItemByExecutionId(int $executionId): ?TransactionLog
    {
        $queryFilter = new QueryFilter();
        $queryFilter
            ->where('executionId', Operators::EQUALS, $executionId)
            ->orderBy('id', QueryFilter::ORDER_DESC);

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->repository->selectOne($queryFilter);
    }

    /**
     * @param DateTime|null $disconnectTime
     * @return int
     *
     * @throws QueryFilterInvalidParamException
     */
    public function count(?DateTime $disconnectTime = null): int
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId());

        if ($disconnectTime) {
            $queryFilter->where('timestamp', Operators::GREATER_THAN, $disconnectTime);
        }

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->repository->count($queryFilter);
    }

    /**
     * @inheritDoc
     */
    public function findByMerchantReference(string $merchantReference): ?TransactionLog
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('merchantReference', Operators::EQUALS, $merchantReference);

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->repository->selectOne($queryFilter);
    }

    /**
     * @inheritDoc
     */
    public function logsExist(DateTime $beforeDate): bool
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('timestamp', Operators::LESS_THAN, $beforeDate->getTimestamp())
            ->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId());
        $result = $this->repository->count($queryFilter);

        return $result > 0;
    }

    /**
     * @inheritDoc
     */
    public function deleteLogs(DateTime $beforeDate, int $limit): void
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('timestamp', Operators::LESS_THAN, $beforeDate->getTimestamp())
            ->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId());
        $queryFilter->setLimit($limit);

        $this->repository->deleteWhere($queryFilter);
    }

    /**
     * @param string $merchantReference
     *
     * @return TransactionLog|null
     *
     * @throws QueryFilterInvalidParamException
     */
    protected function getTransactionLogEntity(string $merchantReference): ?TransactionLog
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId())
            ->where('merchantReference', Operators::EQUALS, $merchantReference);

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->repository->selectOne($queryFilter);
    }
}
