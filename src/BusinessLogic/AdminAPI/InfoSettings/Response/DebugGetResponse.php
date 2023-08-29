<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\InfoSettings\Response;

use Adyen\Core\BusinessLogic\AdminAPI\Response\Response;

/**
 * Class DebugGetResponse
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\InfoSettings\Response
 */
class DebugGetResponse extends Response
{
    /**
     * @var bool
     */
    private $debugMode;

    /**
     * @param bool $debugMode
     */
    public function __construct(bool $debugMode)
    {
        $this->debugMode = $debugMode;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return ['debugMode' => $this->debugMode];
    }
}
