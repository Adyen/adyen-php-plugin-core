<?php

namespace Adyen\Core\BusinessLogic\Domain\ShopNotifications\Services;

use Adyen\Core\BusinessLogic\DataAccess\Disconnect\Repositories\DisconnectRepository;
use Adyen\Core\BusinessLogic\DataAccess\Notifications\Entities\Notification;
use Adyen\Core\BusinessLogic\Domain\Integration\ShopNotifications\ShopNotificationChannelAdapter;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Event;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Severity;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Repositories\ShopNotificationRepository;
use DateTime;
use Exception;

/**
 * Class ShopNotificationService
 *
 * @package Adyen\Core\BusinessLogic\Domain\ShopNotifications\Services
 */
class ShopNotificationService
{
    /**
     * @var ShopNotificationRepository
     */
    private $shopNotificationRepository;
    /**
     * @var ShopNotificationChannelAdapter
     */
    private $shopNotificationChannelAdapter;
    /**
     * @var DisconnectRepository
     */
    private $disconnectRepository;

    /**
     * @param ShopNotificationRepository $shopNotificationRepository
     * @param ShopNotificationChannelAdapter $shopNotificationChannelAdapter
     * @param DisconnectRepository $disconnectRepository
     */
    public function __construct(
        ShopNotificationRepository     $shopNotificationRepository,
        ShopNotificationChannelAdapter $shopNotificationChannelAdapter,
        DisconnectRepository $disconnectRepository
    )
    {
        $this->shopNotificationRepository = $shopNotificationRepository;
        $this->shopNotificationChannelAdapter = $shopNotificationChannelAdapter;
        $this->disconnectRepository = $disconnectRepository;
    }

    /**
     * @param int $limit
     * @param int $offset
     *
     * @return Notification[]
     *
     * @throws Exception
     */
    public function getNotifications(int $limit, int $offset): array
    {
        $disconnectTime = $this->disconnectRepository->getDisconnectTime();

        return $this->shopNotificationRepository->getNotifications($limit, $offset, $disconnectTime);
    }

    /**
     * @param Event $event
     *
     * @return void
     */
    public function pushNotification(Event $event): void
    {
        $this->shopNotificationRepository->pushNotification($event);

        if ($event->getSeverity()->isSignificant()) {
            $this->shopNotificationChannelAdapter->push($event);
        }
    }

    /**
     * @param int $page
     * @param int $limit
     *
     * @return bool
     *
     * @throws Exception
     */
    public function hasNextPage(int $page, int $limit): bool
    {
        $disconnectTime = $this->disconnectRepository->getDisconnectTime();
        $count = $this->shopNotificationRepository->count($disconnectTime);

        if ($page <= 1) {
            return $limit < $count;
        }

        return $page * $limit < $count;
    }

    /**
     * @param DateTime $dateTime
     * @param array $severity
     *
     * @return bool
     *
     * @throws Exception
     */
    public function hasSignificantNotifications(
        DateTime $dateTime,
        array    $severity = [Severity::WARNING, Severity::ERROR]
    ): bool
    {
        return $this->shopNotificationRepository->countSignificantNotifications($dateTime, $severity) > 0;
    }

    /**
     * Deletes notifications before given date.
     *
     * @param DateTime $beforeDate
     * @param int $limit
     *
     * @return void
     *
     * @throws Exception
     */
    public function deleteNotifications(DateTime $beforeDate, int $limit): void
    {
        $this->shopNotificationRepository->deleteNotifications($beforeDate, $limit);
    }
}
