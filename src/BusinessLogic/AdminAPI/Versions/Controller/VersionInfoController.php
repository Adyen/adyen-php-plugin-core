<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\Versions\Controller;

use Adyen\Core\BusinessLogic\AdminAPI\Versions\Response\VersionInfoResponse;
use Adyen\Core\BusinessLogic\Domain\Integration\Version\VersionService;

/**
 * Class VersionInfoController
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\Versions\Controller
 */
class VersionInfoController
{
    /**
     * @var VersionService
     */
    private $service;

    /**
     * @param VersionService $service
     */
    public function __construct(VersionService $service)
    {
        $this->service = $service;
    }

    /**
     * @return VersionInfoResponse
     */
    public function getVersionInfo(): VersionInfoResponse
    {
        return new VersionInfoResponse($this->service->getVersionInfo());
    }
}
