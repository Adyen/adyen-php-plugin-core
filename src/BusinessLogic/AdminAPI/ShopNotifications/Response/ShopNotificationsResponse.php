<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\ShopNotifications\Response;

use Adyen\Core\BusinessLogic\AdminAPI\Response\Response;
use Adyen\Core\BusinessLogic\DataAccess\Notifications\Entities\Notification;
use Adyen\Core\Infrastructure\Utility\TimeProvider;
use DateTimeInterface;

/**
 * Class ShopNotificationsResponseR
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\ShopNotifications\Request
 */
class ShopNotificationsResponse extends Response
{
    /**
     * @var bool
     */
    private $hasNextPage;

    /**
     * @var Notification[]
     */
    private $notifications;

    /**
     * @param bool $hasNextPage
     * @param Notification[] $notifications
     */
    public function __construct(bool $hasNextPage, array $notifications)
    {
        $this->hasNextPage = $hasNextPage;
        $this->notifications = $notifications;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'nextPageAvailable' => $this->hasNextPage,
            'notifications' => $this->notificationsToArray($this->notifications)
        ];
    }

    /**
     * @param Notification[] $notifications
     *
     * @return array
     */
    private function notificationsToArray(array $notifications): array
    {
        $notificationsToArray = [];

        foreach ($notifications as $notification) {
            $notificationsToArray[] = [
                'orderId' => $notification->getOrderId(),
                'paymentMethod' => $notification->getPaymentMethod(),
                'severity' => $notification->getSeverity(),
                'dateAndTime' => TimeProvider::getInstance()
                    ->getDateTime($notification->getTimestamp())
                    ->format(DateTimeInterface::ATOM),
                'message' => $notification->getMessage()->getCode(),
                'details' => $notification->getDetails()->getCode()
            ];
        }

        return $notificationsToArray;
    }
}
