<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\Connection\Controller;

use Adyen\Core\BusinessLogic\AdminAPI\Connection\Request\ConnectionRequest;
use Adyen\Core\BusinessLogic\AdminAPI\Connection\Response\ConnectionResponse;
use Adyen\Core\BusinessLogic\AdminAPI\Connection\Response\ConnectionSettingsResponse;
use Adyen\Core\BusinessLogic\AdyenAPI\Exceptions\ConnectionSettingsNotFoundException;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\ApiCredentialsDoNotExistException;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\ApiKeyCompanyLevelException;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\EmptyConnectionDataException;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\EmptyStoreException;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidAllowedOriginException;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidApiKeyException;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidConnectionSettingsException;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidModeException;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\MerchantIdChangedException;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\ModeChangedException;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\UserDoesNotHaveNecessaryRolesException;
use Adyen\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use Adyen\Core\BusinessLogic\Domain\Merchant\Exceptions\ClientKeyGenerationFailedException;
use Adyen\Core\BusinessLogic\Domain\Webhook\Exceptions\FailedToGenerateHmacException;
use Adyen\Core\BusinessLogic\Domain\Webhook\Exceptions\FailedToRegisterWebhookException;
use Adyen\Core\BusinessLogic\Domain\Webhook\Exceptions\MerchantDoesNotExistException;

/**
 * Class ConnectionController
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\Connection\Controller
 */
class ConnectionController
{
    /**
     * @var ConnectionService
     */
    private $connectionService;

    /**
     * @param ConnectionService $connectionService
     */
    public function __construct(ConnectionService $connectionService)
    {
        $this->connectionService = $connectionService;
    }

    /**
     * @param ConnectionRequest $connectionRequest
     *
     * @return ConnectionResponse
     *
     * @throws ApiCredentialsDoNotExistException
     * @throws ApiKeyCompanyLevelException
     * @throws ClientKeyGenerationFailedException
     * @throws EmptyConnectionDataException
     * @throws EmptyStoreException
     * @throws FailedToGenerateHmacException
     * @throws FailedToRegisterWebhookException
     * @throws InvalidAllowedOriginException
     * @throws InvalidApiKeyException
     * @throws InvalidConnectionSettingsException
     * @throws InvalidModeException
     * @throws MerchantIdChangedException
     * @throws ModeChangedException
     * @throws UserDoesNotHaveNecessaryRolesException
     * @throws ConnectionSettingsNotFoundException
     * @throws MerchantDoesNotExistException
     */
    public function connect(ConnectionRequest $connectionRequest): ConnectionResponse
    {
        $this->connectionService->saveConnectionData($connectionRequest->transformToDomainModel());

        return new ConnectionResponse();
    }

    /**
     * @return ConnectionSettingsResponse
     */
    public function getConnectionSettings(): ConnectionSettingsResponse
    {
        return new ConnectionSettingsResponse($this->connectionService->getConnectionData());
    }
}
