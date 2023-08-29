<?php

namespace Adyen\Core\BusinessLogic\Domain\ShopNotifications\Repositories;

use Adyen\Core\BusinessLogic\DataAccess\Notifications\Entities\Notification;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Event;
use DateTime;
use Exception;

/**
 * Interface ShopNotificationRepository
 *
 * @package Adyen\Core\BusinessLogic\Domain\ShopNotifications\Repositories
 */
interface ShopNotificationRepository
{
    /**
     * Fetch shop notifications for current store context.
     *
     * @param int $limit
     * @param int $offset
     * @param DateTime|null $disconnectTime
     *
     * @return array
     */
    public function getNotifications(int $limit, int $offset, ?DateTime $disconnectTime = null): array;

    /**
     * Insert notification to database.
     *
     * @param Event $event
     *
     * @return void
     */
    public function pushNotification(Event $event): void;

    /**
     * Returns count of shop notifications in database.
     *
     * @param DateTime|null $disconnectTime
     *
     * @return int
     */
    public function count(?DateTime $disconnectTime = null): int;

    /**
     * Returns number of significant notifications since given date.
     *
     * @param DateTime $dateTime
     * @param array $severity
     *
     * @return int
     *
     * @throws Exception
     */
    public function countSignificantNotifications(DateTime $dateTime, array $severity): int;

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
    public function deleteNotifications(DateTime $beforeDate, int $limit): void;
}
