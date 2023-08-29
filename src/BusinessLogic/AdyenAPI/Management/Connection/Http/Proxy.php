<?php

namespace Adyen\Core\BusinessLogic\AdyenAPI\Management\Connection\Http;

use Adyen\Core\BusinessLogic\AdyenAPI\Http\Authorized\AuthorizedProxy;
use Adyen\Core\BusinessLogic\AdyenAPI\Http\Requests\HttpRequest;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ApiCredentials;
use Adyen\Core\BusinessLogic\Domain\Connection\Proxies\ConnectionProxy;
use Adyen\Core\Infrastructure\Http\Exceptions\HttpRequestException;
use Adyen\Core\Infrastructure\Logger\Logger;

/**
 * Class Proxy
 *
 * @package Adyen\Core\BusinessLogic\AdyenAPI\Connection\Http
 */
class Proxy extends AuthorizedProxy implements ConnectionProxy
{
    /**
     * @inheritDoc
     */
    public function getApiCredentialDetails(): ?ApiCredentials
    {
        $request = new HttpRequest('/me');
        try {
            $response = $this->get($request);
        } catch (HttpRequestException $e) {
            Logger::logError($e->getMessage());

            return null;
        }

        $responseBody = $response->decodeBodyToArray();

        return new ApiCredentials(
            $responseBody['id'] ?? '',
            $responseBody['active'] ?? false,
            $responseBody['companyName'] ?? ''
        );
    }

    /**
     * @inheritDoc
     */
    public function addAllowedOrigin(string $domain): bool
    {
        $request = new HttpRequest('/me/allowedOrigins', ['domain' => $domain]);

        try {
            $response = $this->post($request)->decodeBodyToArray();
        } catch (HttpRequestException $e) {
            Logger::logError($e->getMessage());

            return false;
        }

        return !empty($response['id']);
    }

    /**
     * @inheritDoc
     *
     * @throws HttpRequestException
     */
    public function hasAllowedOrigin(string $domain): bool
    {
        $request = new HttpRequest('/me/allowedOrigins');
        $response = $this->get($request);

        return $this->checkIfDomainExists($response->decodeBodyToArray(), $domain);
    }

    /**
     * @inheritDoc
     */
    public function getUserRoles(): array
    {
        $request = new HttpRequest('/me');
        try {
            $response = $this->get($request);
        } catch (HttpRequestException $e) {
            Logger::logError($e->getMessage());

            return [];
        }

        $responseBody = $response->decodeBodyToArray();

        return $responseBody['roles'] ?? [];
    }

    public function isMerchantLevelKey(): bool
    {
        $request = new HttpRequest('/companies');

        try {
            $response = $this->get($request);
        } catch (HttpRequestException $e) {
            Logger::logError($e->getMessage());

            return false;
        }

        $responseBody = $response->decodeBodyToArray();

        return empty($responseBody['data']);
    }

    /**
     * Check if domain is in allowed origins.
     *
     * @param array $allowedOrigins
     * @param string $domain
     *
     * @return bool
     */
    private function checkIfDomainExists(array $allowedOrigins, string $domain): bool
    {
        if (!isset($allowedOrigins['data'])) {
            return false;
        }

        foreach ($allowedOrigins['data'] as $origin) {
            if ($origin['domain'] === $domain) {
                return true;
            }
        }

        return false;
    }
}
