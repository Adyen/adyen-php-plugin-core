<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\TestConnection\Controller;

use Adyen\Core\BusinessLogic\AdminAPI\Connection\Request\ConnectionRequest;
use Adyen\Core\BusinessLogic\AdminAPI\TestConnection\Response\TestConnectionResponse;
use Adyen\Core\BusinessLogic\AdyenAPI\Exceptions\ConnectionSettingsNotFoundException;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\ApiCredentialsDoNotExistException;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\ApiKeyCompanyLevelException;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\EmptyConnectionDataException;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\EmptyStoreException;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidApiKeyException;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidConnectionSettingsException;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidModeException;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\MerchantIdChangedException;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\ModeChangedException;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\UserDoesNotHaveNecessaryRolesException;
use Adyen\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use Adyen\Core\BusinessLogic\Domain\Webhook\Exceptions\MerchantDoesNotExistException;

/**
 * Class TestConnectionController
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\TestConnection\Controller
 */
class TestConnectionController
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
     * @return TestConnectionResponse
     *
     * @throws ApiCredentialsDoNotExistException
     * @throws InvalidConnectionSettingsException
     * @throws MerchantIdChangedException
     * @throws ModeChangedException
     * @throws UserDoesNotHaveNecessaryRolesException
     * @throws ConnectionSettingsNotFoundException
     * @throws ApiKeyCompanyLevelException
     * @throws EmptyConnectionDataException
     * @throws EmptyStoreException
     * @throws InvalidApiKeyException
     * @throws InvalidModeException
     * @throws MerchantDoesNotExistException
     */
    public function test(ConnectionRequest $connectionRequest): TestConnectionResponse
    {
        $this->connectionService->validateConnection($connectionRequest->transformToDomainModel());

        return new TestConnectionResponse(true, 'Connection with Adyen is valid');
    }
}
