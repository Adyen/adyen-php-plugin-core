<?php

namespace Adyen\Core\BusinessLogic\Domain\Connection\Proxies;

use Adyen\Core\BusinessLogic\Domain\Connection\Models\ApiCredentials;

/**
 * Class ConnectionProxy
 *
 * @package Adyen\Core\BusinessLogic\Domain\Connection\Proxies
 */
interface ConnectionProxy
{
    /**
     * Retrieves API credential details.
     *
     * @return ApiCredentials | null
     */
    public function getApiCredentialDetails(): ?ApiCredentials;

    /**
     * Registers the shop domain to the list of allowed origins. Returns true if success
     *
     * @param string $domain
     *
     * @return void
     */
    public function addAllowedOrigin(string $domain): bool;

    /**
     * Calls /me/allowedOrigins endpoint and check if domain exists.
     *
     * @param string $domain
     *
     * @return bool
     */
    public function hasAllowedOrigin(string $domain): bool;

    /**
     * Calls /me endpoint and retrieves array of user roles.
     *
     * @return array
     */
    public function getUserRoles(): array;

    /**
     * Checks if provided API key is merchant level.
     *
     * @return bool
     */
    public function isMerchantLevelKey(): bool;
}
