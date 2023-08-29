<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\Integration\Controller;

use Adyen\Core\BusinessLogic\AdminAPI\Integration\Response\StateResponse;
use Adyen\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;

/**
 * Class IntegrationController
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\Integration\Controller
 */
class IntegrationController
{
    /**
     * @var ConnectionService
     */
    private $authorizationService;

    /**
     * @param ConnectionService $authorizationService
     */
    public function __construct(ConnectionService $authorizationService)
    {
        $this->authorizationService = $authorizationService;
    }

    /**
     * Check if user is loggedIn. If true return onboarding state response, otherwise dashboard state.
     *
     * @return StateResponse
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function getState(): StateResponse
    {
        return $this->authorizationService->isLoggedIn() ? StateResponse::dashboard() : StateResponse::onboarding();
    }
}
