<?php

namespace Adyen\Core\BusinessLogic\DataAccess\Notifications\Repositories;

use Adyen\Core\BusinessLogic\DataAccess\Notifications\Contracts\ShopNotificationRepository as IntegrationShopNotificationRepository;
use Adyen\Core\BusinessLogic\DataAccess\Notifications\Entities\Notification as NotificationEntity;
use Adyen\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Event;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Repositories\ShopNotificationRepository as ShopNotificationRepositoryInterface;
use Adyen\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use Adyen\Core\Infrastructure\ORM\QueryFilter\Operators;
use Adyen\Core\Infrastructure\ORM\QueryFilter\QueryFilter;
use DateTime;

/**
 * Class ShopNotificationRepository
 *
 * @package Adyen\Core\BusinessLogic\DataAccess\Notifications\Repositories
 */
class ShopNotificationRepository implements ShopNotificationRepositoryInterface
{
    /**
     * @var IntegrationShopNotificationRepository
     */
    protected $repository;

    /**
     * @var StoreContext
     */
    protected $storeContext;

    /**
     * @param IntegrationShopNotificationRepository $repository
     * @param StoreContext $storeContext
     */
    public function __construct(IntegrationShopNotificationRepository $repository, StoreContext $storeContext)
    {
        $this->repository = $repository;
        $this->storeContext = $storeContext;
    }

    /**
     * @inheritDoc
     *
     * @throws QueryFilterInvalidParamException
     */
    public function getNotifications(int $limit, int $offset, ?DateTime $disconnectTime = null): array
    {
        $queryFilter = new QueryFilter();
        $queryFilter->setLimit($limit);
        $queryFilter->setOffset($offset);
        $queryFilter->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId())->orderBy(
            'id',
            QueryFilter::ORDER_DESC
        );

        if ($disconnectTime) {
            $queryFilter->where('timestamp', Operators::GREATER_THAN, $disconnectTime->getTimestamp());
        }

        return $this->repository->select($queryFilter);
    }

    /**
     * @inheritDoc
     */
    public function pushNotification(Event $event): void
    {
        $entity = new NotificationEntity();
        $entity->setStoreId($this->storeContext->getStoreId());
        $entity->setOrderId($event->getOrderId());
        $entity->setPaymentMethod($event->getPaymentMethod());
        $entity->setSeverity($event->getSeverity()->getSeverity());
        $entity->setTimestamp($event->getDateAndTime()->getTimestamp());
        $entity->setMessage($event->getMessage());
        $entity->setDetails($event->getDetails());

        $this->repository->save($entity);
    }

    /**
     * @param DateTime|null $disconnectTime
     *
     * @return int
     *
     * @throws QueryFilterInvalidParamException
     */
    public function count(?DateTime $disconnectTime = null): int
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId());

        if ($disconnectTime) {
            $queryFilter->where('timestamp', Operators::GREATER_THAN, $disconnectTime->getTimestamp());
        }

        return $this->repository->count($queryFilter);
    }

    /**
     * @inheritDoc
     */
    public function countSignificantNotifications(DateTime $dateTime, array $severity): int
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('timestamp', Operators::LESS_THAN, $dateTime->getTimestamp())
            ->where('severity', Operators::IN, $severity)
            ->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId());

        return $this->repository->count($queryFilter);
    }

    /**
     * @inheritDoc
     */
    public function deleteNotifications(DateTime $beforeDate, int $limit): void
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('timestamp', Operators::LESS_THAN, $beforeDate->getTimestamp());
        $queryFilter->setLimit($limit);

        $this->repository->deleteWhere($queryFilter);
    }
}
