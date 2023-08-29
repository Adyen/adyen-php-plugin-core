<?php

namespace Adyen\Core\BusinessLogic\Domain\Version\Models;

/**
 * Class VersionInfo
 *
 * @package Adyen\Core\BusinessLogic\Domain\Version\Models
 */
class VersionInfo
{
    /**
     * @var string
     */
    private $installed;
    /**
     * @var string
     */
    private $latest;

    /**
     * @param string $installed
     * @param string $latest
     */
    public function __construct(string $installed, string $latest = '')
    {
        $this->installed = $installed;
        $this->latest = $latest;
    }

    /**
     * @return string
     */
    public function getInstalled(): string
    {
        return $this->installed;
    }

    /**
     * @return string
     */
    public function getLatest(): string
    {
        return $this->latest;
    }
}
