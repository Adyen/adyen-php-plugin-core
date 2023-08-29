<?php

namespace Adyen\Core\Tests\BusinessLogic\Domain\NotificationsRemover\MockComponents;

use Adyen\Core\BusinessLogic\TransactionLog\Services\TransactionLogService;
use DateTime;

class MockTransactionLogService extends TransactionLogService
{
    public $logsExistCalled = false;
    public $deleteCalled = false;

    public function logsExist(DateTime $beforeDate): bool
    {
        $this->logsExistCalled = true;

        return parent::logsExist($beforeDate);
    }

    public function deleteLogs(DateTime $beforeDate, int $limit): void
    {
        $this->deleteCalled = true;

        parent::deleteLogs($beforeDate, $limit);
    }
}
