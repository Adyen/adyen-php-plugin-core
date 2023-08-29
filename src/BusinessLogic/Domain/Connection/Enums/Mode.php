<?php

namespace Adyen\Core\BusinessLogic\Domain\Connection\Enums;

/**
 * Class Mode
 *
 * @package Adyen\Core\BusinessLogic\Domain\Connection\Enums
 */
interface Mode
{
    /**
     * String representation of live mode
     */
    public const MODE_LIVE = 'live';

    /**
     * String representation of test mode
     */
    public const MODE_TEST = 'test';
}
