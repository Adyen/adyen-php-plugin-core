<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\Disconnect\Controller;

use Adyen\Core\BusinessLogic\AdminAPI\Disconnect\Response\DisconnectResponse;
use Adyen\Core\BusinessLogic\Domain\Disconnect\Services\DisconnectService;
use Exception;

/**
 * Class DisconnectController
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\Disconnect\Controller
 */
class DisconnectController
{
    /**
     * @var DisconnectService
     */
    private $disconnectService;

    /**
     * @param DisconnectService $disconnectService
     */
    public function __construct(DisconnectService $disconnectService)
    {
        $this->disconnectService = $disconnectService;
    }

    /**
     * Disconnects account.
     *
     * @return DisconnectResponse
     *
     * @throws Exception
     */
    public function disconnect(): DisconnectResponse
    {
        $this->disconnectService->disconnect();

        return new DisconnectResponse();
    }
}
