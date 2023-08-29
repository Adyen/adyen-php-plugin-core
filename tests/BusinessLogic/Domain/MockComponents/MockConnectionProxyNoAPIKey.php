<?php

namespace Adyen\Core\Tests\BusinessLogic\Domain\MockComponents;

use Adyen\Core\BusinessLogic\Domain\Connection\Models\ApiCredentials;
use Adyen\Core\BusinessLogic\Domain\Connection\Proxies\ConnectionProxy;

/**
 * Class MockConnectionProxyNoAPIKey
 *
 * @package Adyen\Core\Tests\BusinessLogic\Domain\MockComponents
 */
class MockConnectionProxyNoAPIKey implements ConnectionProxy
{
	/**
	 * @inheritDoc
	 */
	public function getApiCredentialDetails(): ?ApiCredentials
	{
		return new ApiCredentials('1234', false, 'test');
	}

	/**
	 * @inheritDoc
	 */
	public function addAllowedOrigin(string $domain): bool
	{
		return false;
	}

    public function hasAllowedOrigin(string $domain): bool
    {
        return false;
    }

    public function getUserRoles(): array
    {
        return [
            'Management API - Accounts read',
            'Management API - Webhooks read',
            'Management API - API credentials read and write',
            'Management API - Stores read',
            'Management API — Payment methods read',
            'Management API - Stores read and write',
            'Management API - Webhooks read and write',
            'Checkout encrypted cardholder data',
            'Merchant Recurring role',
            'Data Protection API',
            'Management API - Payout Account Settings Read',
            'Checkout webservice role',
            'Management API - Accounts read and write',
            'Merchant PAL Webservice role'
        ];
    }

    public function isMerchantLevelKey(): bool
    {
        return true;
    }
}
