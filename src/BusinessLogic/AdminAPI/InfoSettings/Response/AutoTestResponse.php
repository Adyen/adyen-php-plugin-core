<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\InfoSettings\Response;

use Adyen\Core\BusinessLogic\AdminAPI\Response\Response;
use Adyen\Core\Infrastructure\AutoTest\AutoTestStatus;
use Adyen\Core\Infrastructure\TaskExecution\QueueItem;

/**
 * Class AutoTestResponse
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\InfoSettings\Response
 */
class AutoTestResponse extends Response
{
    /**
     * Message for successful webhook validation.
     */
    private const SUCCESS_MESSAGE = 'Auto-test completed successfully';

    /**
     * Message for failed webhook validation.
     */
    private const FAIL_MESSAGE = 'Auto-test did not complete successfully';

    /**
     * @var AutoTestStatus
     */
    private $status;

    /**
     * @param AutoTestStatus $status
     */
    public function __construct(AutoTestStatus $status)
    {
        $this->status = $status;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'finished' => $this->status->finished,
            'status' => $this->status->taskStatus === QueueItem::COMPLETED,
            'message' => $this->status->taskStatus === QueueItem::COMPLETED ? 'auto.test.success' : 'auto.test.fail'
        ];
    }
}
