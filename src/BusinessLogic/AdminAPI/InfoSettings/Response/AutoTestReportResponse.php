<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\InfoSettings\Response;

use Adyen\Core\BusinessLogic\AdminAPI\Response\Response;

/**
 * Class AutoTestReportResponse
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\InfoSettings\Response
 */
class AutoTestReportResponse extends Response
{
    /**
     * @var array
     */
    private $logs;

    /**
     * @param array $logs
     */
    public function __construct(array $logs)
    {
        $this->logs = $logs;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->logs;
    }
}
