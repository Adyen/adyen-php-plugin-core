<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\InfoSettings\Controller;

use Adyen\Core\BusinessLogic\AdminAPI\InfoSettings\Response\DebugGetResponse;
use Adyen\Core\BusinessLogic\AdminAPI\InfoSettings\Response\DebugPutResponse;
use Adyen\Core\Infrastructure\Configuration\Configuration;

/**
 * Class DebugController
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\InfoSettings\Controller
 */
class DebugController
{
    /**
     * @var Configuration
     */
    private $configurationService;

    /**
     * @param Configuration $configurationService
     */
    public function __construct(Configuration $configurationService)
    {
        $this->configurationService = $configurationService;
    }

    /**
     * @return DebugGetResponse
     */
    public function getDebugMode(): DebugGetResponse
    {
        return new DebugGetResponse($this->configurationService->isDebugModeEnabled());
    }

    /**
     * @param bool $debugMode
     *
     * @return DebugPutResponse
     */
    public function setDebugMode(bool $debugMode): DebugPutResponse
    {
        $this->configurationService->setDebugModeEnabled($debugMode);

        return new DebugPutResponse();
    }
}
