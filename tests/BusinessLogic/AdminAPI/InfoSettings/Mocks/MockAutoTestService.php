<?php

namespace Adyen\Core\Tests\BusinessLogic\AdminAPI\InfoSettings\Mocks;

use Adyen\Core\Infrastructure\AutoTest\AutoTestService;
use RuntimeException;

/**
 * Class MockAutoTestService
 *
 * @package Adyen\Core\Tests\BusinessLogic\AdminAPI\InfoSettings\Mocks
 */
class MockAutoTestService extends AutoTestService
{
    public $callHistory = [];
    public $startAutoTestResult = 1;
    public $getAutoTestTaskStatusResult = null;
    public $shouldFail = false;
    public $failureMessage = 'Failure message.';

    /**
     * @return int|mixed
     */
    public function startAutoTest(): int
    {
        $this->callHistory[] = 'startAutoTest';

        if ($this->shouldFail) {
            throw new RuntimeException($this->failureMessage);
        }

        return $this->startAutoTestResult;
    }

    /**
     * @param $loggerInitializerDelegate
     *
     * @return void
     */
    public function stopAutoTestMode($loggerInitializerDelegate): void
    {
        $this->callHistory[] = 'stopAutoTestMode';

        if ($this->shouldFail) {
            throw new RuntimeException($this->failureMessage);
        }
    }

    /**
     * @param $queueItemId
     *
     * @return mixed|null
     */
    public function getAutoTestTaskStatus($queueItemId = 0)
    {
        $this->callHistory[] = 'getAutoTestTaskStatus';

        if ($this->shouldFail) {
            throw new RuntimeException($this->failureMessage);
        }

        return $this->getAutoTestTaskStatusResult;
    }
}
