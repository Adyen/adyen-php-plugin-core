<?php

namespace Adyen\Core\BusinessLogic\DataAccess\TaskCleanup\Repositories;

use Adyen\Core\BusinessLogic\Domain\TaskCleanup\Interfaces\TaskCleanupRepository as TaskCleanupRepositoryInterface;
use Adyen\Core\BusinessLogic\ORM\Interfaces\QueueItemRepository;
use Adyen\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use Adyen\Core\Infrastructure\ORM\QueryFilter\Operators;
use Adyen\Core\Infrastructure\ORM\QueryFilter\QueryFilter;
use Adyen\Core\Infrastructure\TaskExecution\QueueItem;
use DateTime;

/**
 * Class TaskCleanupRepository
 *
 * @package Adyen\Core\BusinessLogic\DataAccess\TaskCleanup\Repositories
 */
class TaskCleanupRepository implements TaskCleanupRepositoryInterface
{
    /**
     * @var QueueItemRepository
     */
    protected $repository;

    /**
     * @param QueueItemRepository $repository
     */
    public function __construct(QueueItemRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return int
     *
     * @throws QueryFilterInvalidParamException
     */
    public function getCompletedCount(): int
    {
        $filter = new QueryFilter();
        $filter->where('status', Operators::EQUALS, QueueItem::COMPLETED);

        return $this->repository->count($filter);
    }

    /**
     * @return int
     *
     * @throws QueryFilterInvalidParamException
     */
    public function getFailedCount(): int
    {
        $filter = new QueryFilter();
        $filter->where('status', Operators::IN, [QueueItem::FAILED, QueueItem::ABORTED]);

        return $this->repository->count($filter);
    }

    /**
     * @param int $limit
     *
     * @return void
     *
     * @throws QueryFilterInvalidParamException
     */
    public function deleteCompletedTasks(int $limit = 5000): void
    {
        $filter = new QueryFilter();
        $filter->where('status', Operators::EQUALS, QueueItem::COMPLETED);
        $filter->setLimit($limit);

        $this->repository->deleteWhere($filter);
    }

    /**
     * @param DateTime $beforeDate
     * @param int $limit
     *
     * @return void
     *
     * @throws QueryFilterInvalidParamException
     */
    public function deleteFailedTasks(DateTime $beforeDate, int $limit = 5000): void
    {
        $filter = new QueryFilter();
        $filter->where('status', Operators::IN, [QueueItem::FAILED, QueueItem::ABORTED])
            ->where(
                'queueTime',
                Operators::LESS_THAN,
                $beforeDate
            );
        $filter->setLimit($limit);

        $this->repository->deleteWhere($filter);
    }
}
