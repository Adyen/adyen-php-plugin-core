<?php

namespace Adyen\Core\Tests\Infrastructure\TaskExecution;

use Adyen\Core\Infrastructure\TaskExecution\Interfaces\TaskRunnerManager;
use Adyen\Core\Infrastructure\TaskExecution\Interfaces\TaskRunnerWakeup;
use Adyen\Core\Tests\Infrastructure\Common\BaseInfrastructureTestWithServices;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\TestTaskRunnerWakeupService;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;

class TaskRunnerManagerTest extends BaseInfrastructureTestWithServices
{
    /**
     * @var \Adyen\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\TestTaskRunnerWakeupService
     */
    protected $taskRunnerWakeup;

    public function setUp(): void
    {
        parent::setUp();

        $testTaskRunnerWakeupService = new TestTaskRunnerWakeupService();
        $this->taskRunnerWakeup = $testTaskRunnerWakeupService;

        TestServiceRegister::registerService(
            TaskRunnerWakeup::CLASS_NAME,
            function () use ($testTaskRunnerWakeupService) {
                return $testTaskRunnerWakeupService;
            }
        );

        TestServiceRegister::registerService(
            TaskRunnerManager::CLASS_NAME,
            function () {
                return new \Adyen\Core\Infrastructure\TaskExecution\TaskRunnerManager();
            }
        );
    }

    /**
     * Tests default task runner halted value.
     */
    public function testDefaultHaltedStatus()
    {
        // assert
        $this->assertFalse($this->shopConfig->isTaskRunnerHalted());
    }

    /**
     * Tests that task runner manager properly halts task runner.
     */
    public function testTaskRunnerHalt()
    {
        // arrange
        /** @var TaskRunnerManager $taskRunnerManager */
        $taskRunnerManager = TestServiceRegister::getService(TaskRunnerManager::CLASS_NAME);

        // act
        $taskRunnerManager->halt();

        // assert
        $this->assertTrue($this->shopConfig->isTaskRunnerHalted());
    }

    /**
     * Asserts that task runner is properly resumed.
     */
    public function testTaskRunnerResume()
    {
        // arrange
        $this->shopConfig->setTaskRunnerHalted(true);

        /** @var TaskRunnerManager $taskRunnerManager */
        $taskRunnerManager = TestServiceRegister::getService(TaskRunnerManager::CLASS_NAME);

        // act
        $taskRunnerManager->resume();

        // assert
        $this->assertFalse($this->shopConfig->isTaskRunnerHalted());
        $this->assertNotEmpty($this->taskRunnerWakeup->getMethodCallHistory('wakeup'));
    }
}
