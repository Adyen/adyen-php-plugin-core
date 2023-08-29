<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\InfoSettings\Response;

use Adyen\Core\BusinessLogic\AdminAPI\Response\Response;

/**
 * Class WebhookReportResponse
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\InfoSettings\Response
 */
class WebhookReportResponse extends Response
{
    /**
     * @var string
     */
    private $report;

    /**
     * @param string $report
     */
    public function __construct(string $report)
    {
        $this->report = $report;
    }

    /**
     * @return string[]
     */
    public function toArray(): array
    {
        return ['report' => $this->report];
    }
}
