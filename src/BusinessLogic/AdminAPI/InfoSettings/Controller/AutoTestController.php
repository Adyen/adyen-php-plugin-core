<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\InfoSettings\Controller;

use Adyen\Core\BusinessLogic\AdminAPI\InfoSettings\Response\AutoTestReportResponse;
use Adyen\Core\BusinessLogic\AdminAPI\InfoSettings\Response\AutoTestResponse;
use Adyen\Core\BusinessLogic\AdminAPI\InfoSettings\Response\AutoTestStartResponse;
use Adyen\Core\Infrastructure\AutoTest\AutoTestLogger;
use Adyen\Core\Infrastructure\AutoTest\AutoTestService;
use Adyen\Core\Infrastructure\Exceptions\StorageNotAccessibleException;
use Adyen\Core\Infrastructure\Logger\Interfaces\ShopLoggerAdapter;
use Adyen\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use Adyen\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use Adyen\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use Adyen\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;

/**
 * Class AutoTestController
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\InfoSettings\Controller
 */
class AutoTestController
{
    /**
     * @var AutoTestService
     */
    private $autoTestService;

    /**
     * @var ShopLoggerAdapter
     */
    private $shopLogger;

    /**
     * @param AutoTestService $autoTestService
     * @param ShopLoggerAdapter $adapter
     */
    public function __construct(AutoTestService $autoTestService, ShopLoggerAdapter $adapter)
    {
        $this->autoTestService = $autoTestService;
        $this->shopLogger = $adapter;
    }

    /**
     * @return AutoTestStartResponse
     *
     * @throws StorageNotAccessibleException
     * @throws QueueStorageUnavailableException
     */
    public function startAutoTest(): AutoTestStartResponse
    {
        return new AutoTestStartResponse($this->autoTestService->startAutoTest());
    }

    /**
     * @param int $queueItemId
     *
     * @return AutoTestResponse
     *
     * @throws QueryFilterInvalidParamException
     * @throws RepositoryClassException
     * @throws RepositoryNotRegisteredException
     */
    public function autoTestStatus(int $queueItemId): AutoTestResponse
    {
        $status = $this->autoTestService->getAutoTestTaskStatus($queueItemId);

        if ($status->finished) {
            $this->autoTestService->stopAutoTestMode(
                function () {
                    return $this->shopLogger;
                }
            );
        }

        return new AutoTestResponse($status);
    }

    /**
     * @return AutoTestReportResponse
     *
     * @throws RepositoryNotRegisteredException
     */
    public function autoTestReport(): AutoTestReportResponse
    {
        return new AutoTestReportResponse(AutoTestLogger::getInstance()->getLogsArray());
    }
}
