<?php

namespace Adyen\Core\BusinessLogic\Domain\InfoSettings\Models;

/**
 * Class SystemInfo
 *
 * @package Adyen\Core\BusinessLogic\Domain\InfoSettings\Models
 */
class SystemInfo
{
    /**
     * @var string
     */
    private $systemVersion;
    /**
     * @var string
     */
    private $pluginVersion;
    /**
     * @var string
     */
    private $mainThemeName;
    /**
     * @var string
     */
    private $shopUrl;
    /**
     * @var string
     */
    private $adminUrl;
    /**
     * @var string
     */
    private $asyncProcessUrl;
    /**
     * @var string
     */
    private $databaseName;
    /**
     * @var string
     */
    private $databaseVersion;

    /**
     * @param string $systemVersion
     * @param string $pluginVersion
     * @param string $mainThemeName
     * @param string $shopUrl
     * @param string $adminUrl
     * @param string $asyncProcessUrl
     * @param string $databaseName
     * @param string $databaseVersion
     */
    public function __construct(
        string $systemVersion,
        string $pluginVersion,
        string $mainThemeName,
        string $shopUrl,
        string $adminUrl,
        string $asyncProcessUrl,
        string $databaseName,
        string $databaseVersion
    ) {
        $this->systemVersion = $systemVersion;
        $this->pluginVersion = $pluginVersion;
        $this->mainThemeName = $mainThemeName;
        $this->shopUrl = $shopUrl;
        $this->adminUrl = $adminUrl;
        $this->asyncProcessUrl = $asyncProcessUrl;
        $this->databaseName = $databaseName;
        $this->databaseVersion = $databaseVersion;
    }

    /**
     * @return string
     */
    public function getSystemVersion(): string
    {
        return $this->systemVersion;
    }

    /**
     * @return string
     */
    public function getPluginVersion(): string
    {
        return $this->pluginVersion;
    }

    /**
     * @return string
     */
    public function getMainThemeName(): string
    {
        return $this->mainThemeName;
    }

    /**
     * @return string
     */
    public function getShopUrl(): string
    {
        return $this->shopUrl;
    }

    /**
     * @return string
     */
    public function getAdminUrl(): string
    {
        return $this->adminUrl;
    }

    /**
     * @return string
     */
    public function getAsyncProcessUrl(): string
    {
        return $this->asyncProcessUrl;
    }

    /**
     * @return string
     */
    public function getDatabaseName(): string
    {
        return $this->databaseName;
    }

    /**
     * @return string
     */
    public function getDatabaseVersion(): string
    {
        return $this->databaseVersion;
    }
}
