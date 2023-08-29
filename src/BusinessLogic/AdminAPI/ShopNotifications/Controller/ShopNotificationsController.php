<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\ShopNotifications\Controller;

use Adyen\Core\BusinessLogic\AdminAPI\ShopNotifications\Response\ShopNotificationsResponse;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Event;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Services\ShopNotificationService;

/**
 * Class ShopNotificationsController
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\ShopNotifications\Controller
 */
class ShopNotificationsController
{
    /**
     * @var ShopNotificationService
     */
    private $service;

    /**
     * @param ShopNotificationService $service
     */
    public function __construct(ShopNotificationService $service)
    {
        $this->service = $service;
    }

    /**
     * @param int $page
     * @param int $limit
     *
     * @return ShopNotificationsResponse
     */
    public function getNotifications(int $page, int $limit): ShopNotificationsResponse
    {
        return new ShopNotificationsResponse(
            $this->service->hasNextPage($page, $limit),
            $this->service->getNotifications($limit,  ($page - 1) * $limit)
        );
    }

    /**
     * @param Event $event
     *
     * @return void
     */
    public function pushNotification(Event $event): void
    {
        $this->service->pushNotification($event);
    }
}
