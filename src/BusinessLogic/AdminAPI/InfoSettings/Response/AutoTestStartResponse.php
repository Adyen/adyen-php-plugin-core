<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\InfoSettings\Response;

use Adyen\Core\BusinessLogic\AdminAPI\Response\Response;

/**
 * Class AutoTestStartResponse
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\InfoSettings\Response
 */
class AutoTestStartResponse extends Response
{
    /**
     * @var int
     */
    private $queueItemId;

    /**
     * @param int $queueItemId
     */
    public function __construct(int $queueItemId)
    {
        $this->queueItemId = $queueItemId;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return ['queueItemId' => $this->queueItemId];
    }
}
