<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\Integration\Response;

use Adyen\Core\BusinessLogic\AdminAPI\Response\Response;

/**
 * Class StateResponse
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\Integration\Response
 */
class StateResponse extends Response
{
    /**
     * Onboarding string constant.
     */
    private const ONBOARDING = 'onboarding';

    /**
     * Dashboard string constant.
     */
    private const DASHBOARD = 'dashboard';

    /**
     * String representation of state.
     *
     * @var string
     */
    private $state;

    /**
     * @param string $state
     */
    private function __construct(string $state)
    {
        $this->state = $state;
    }

    /**
     * Called when user is loggedIn.
     *
     * @return StateResponse
     */
    public static function onboarding(): self
    {
        return new self(self::ONBOARDING);
    }

    /**
     * Called when user is not loggedIn.
     *
     * @return StateResponse
     */
    public static function dashboard(): self
    {
        return new self(self::DASHBOARD);
    }

    /**
     *  Transforms state to array.
     *
     * @return array Array representation of state.
     */
    public function toArray(): array
    {
        return [
            'state' => $this->state
        ];
    }
}
