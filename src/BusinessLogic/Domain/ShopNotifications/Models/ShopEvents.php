<?php

namespace Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models;

/**
 * Class ShopEvents
 *
 * @package Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models
 */
final class ShopEvents
{
    public const CAPTURE_REQUEST = 'CAPTURE REQUEST';
    public const REFUND_REQUEST = 'REFUND REQUEST';
    public const CANCELLATION_REQUEST = 'CANCELLATION REQUEST';
}
