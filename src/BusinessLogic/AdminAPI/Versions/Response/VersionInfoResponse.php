<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\Versions\Response;

use Adyen\Core\BusinessLogic\AdminAPI\Response\Response;
use Adyen\Core\BusinessLogic\Domain\Version\Models\VersionInfo;

/**
 * Class VersionInfoResponse
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\Versions\Response
 */
class VersionInfoResponse extends Response
{
    /**
     * @var VersionInfo
     */
    private $versionInfo;

    /**
     * @param VersionInfo $versionInfo
     */
    public function __construct(VersionInfo $versionInfo)
    {
        $this->versionInfo = $versionInfo;
    }

    public function toArray(): array
    {
        return [
            'installed' => $this->versionInfo->getInstalled(),
            'latest' => $this->versionInfo->getLatest()
        ];
    }
}
