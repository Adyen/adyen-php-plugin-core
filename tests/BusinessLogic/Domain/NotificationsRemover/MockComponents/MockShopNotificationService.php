<?php

namespace Adyen\Core\Tests\BusinessLogic\Domain\NotificationsRemover\MockComponents;

use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Severity;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Services\ShopNotificationService;
use DateTime;

class MockShopNotificationService extends ShopNotificationService
{
    public $hasNotificationsCalled = false;
    public $deleteCalled = false;

    public function hasSignificantNotifications(DateTime $dateTime, array $severity = [Severity::WARNING, Severity::ERROR]): bool
    {
        $this->hasNotificationsCalled = true;

        return parent::hasSignificantNotifications($dateTime, $severity);
    }

    public function deleteNotifications(DateTime $beforeDate, int $limit): void
    {
        $this->deleteCalled = true;

        parent::deleteNotifications($beforeDate, $limit);
    }
}
