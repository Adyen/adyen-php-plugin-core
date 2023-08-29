<?php

namespace Adyen\Core\BusinessLogic\DataAccess\TransactionHistory\Repositories;

use Adyen\Core\BusinessLogic\DataAccess\Payment\Entities\PaymentMethod;
use Adyen\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Repositories\TransactionHistoryRepository as TransactionHistoryRepositoryInterface;
use Adyen\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use Adyen\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use Adyen\Core\BusinessLogic\DataAccess\TransactionHistory\Entities\TransactionHistory as TransactionEntity;
use Adyen\Core\Infrastructure\ORM\QueryFilter\Operators;
use Adyen\Core\Infrastructure\ORM\QueryFilter\QueryFilter;

/**
 * Class TransactionHistoryRepository
 *
 * @package Adyen\Core\BusinessLogic\DataAccess\TransactionHistory\Repositories
 */
class TransactionHistoryRepository implements TransactionHistoryRepositoryInterface
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
     * @param string $merchantReference
     *
     * @return TransactionHistory|null
     *
     * @throws QueryFilterInvalidParamException
     */
    public function getTransactionHistory(string $merchantReference): ?TransactionHistory
    {
        $entity = $this->getTransactionEntity($merchantReference);

        return $entity ? $entity->getTransactionHistory() : null;
    }

    /**
     * @inheritDoc
     */
    public function getTransactionHistoriesByMerchantReferences(array $merchantReferences): array
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId())
            ->where('merchantReference', Operators::IN, $merchantReferences);

        /** @var TransactionEntity[] $entities */
        $entities = $this->repository->select($queryFilter);
        $result = [];

        foreach ($entities as $entity) {
            if (!$entity->getTransactionHistory()->collection()->isEmpty()) {
                $result[] = $entity->getTransactionHistory();
            }
        }

        return $result;
    }

    /**
     * @inheritDoc
     *
     * @throws QueryFilterInvalidParamException
     */
    public function setTransactionHistory(TransactionHistory $transaction): void
    {
        $existingTransaction = $this->getTransactionEntity($transaction->getMerchantReference());

        if ($existingTransaction) {
            $existingTransaction->setTransactionHistory($transaction);
            $existingTransaction->setStoreId($this->storeContext->getStoreId());
            $existingTransaction->setMerchantReference($transaction->getMerchantReference());
            $this->repository->update($existingTransaction);

            return;
        }

        $entity = new TransactionEntity();
        $entity->setStoreId($this->storeContext->getStoreId());
        $entity->setTransactionHistory($transaction);
        $entity->setMerchantReference($transaction->getMerchantReference());
        $this->repository->save($entity);
    }

    /**
     * @param string $merchantReference
     *
     * @return TransactionEntity|null
     *
     * @throws QueryFilterInvalidParamException
     */
    protected function getTransactionEntity(string $merchantReference): ?TransactionEntity
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId())
            ->where('merchantReference', Operators::EQUALS, $merchantReference);

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->repository->selectOne($queryFilter);
    }
}
