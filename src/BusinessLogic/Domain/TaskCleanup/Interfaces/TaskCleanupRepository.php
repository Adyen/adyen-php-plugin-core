<?php

namespace Adyen\Core\BusinessLogic\Domain\TaskCleanup\Interfaces;

use DateTime;

/**
 * Interface TaskCleanupRepository
 *
 * @package Adyen\Core\BusinessLogic\Domain\TaskCleanup\Interfaces
 */
interface TaskCleanupRepository
{
    /**
     * Gets total count of completed tasks.
     *
     * @return int
     */
    public function getCompletedCount(): int;

    /**
     * Gets total count of failed and aborted tasks.
     *
     * @return int
     */
    public function getFailedCount(): int;

    /**
     * Deletes completed tasks.
     *
     * @param int $limit
     *
     * @return void
     */
    public function deleteCompletedTasks(int $limit = 5000): void;

    /**
     * Deletes failed and aborted tasks queued before date given as first parameter.
     *
     * @param DateTime $beforeDate
     * @param int $limit
     *
     * @return void
     */
    public function deleteFailedTasks(DateTime $beforeDate, int $limit = 5000): void;
}
