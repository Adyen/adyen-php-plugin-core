<?php

namespace Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\AuthorizationAdjustment;

use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Event;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Severity;
use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class SuccessfulAuthorizationAdjustmentEvent.
 *
 * @package Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\AuthorizationAdjustment
 */
class SuccessfulAuthorizationAdjustmentEvent extends Event
{
    /**
     * Message for successful adjustment.
     */
    private const MESSAGE = 'Payment has been successfully adjusted on Adyen.';

    /**
     * Details for failed adjustment.
     */
    private const DETAILS = 'Payment has been successfully adjusted on Adyen.';

    /**
     * @param string $orderId
     * @param string $paymentMethod
     */
    public function __construct(string $orderId, string $paymentMethod)
    {
        parent::__construct(
            $orderId,
            $paymentMethod,
            Severity::info(),
            new TranslatableLabel(self::MESSAGE, 'event.successfulAuthorizationAdjustmentEventMessage'),
            new TranslatableLabel(self::DETAILS, 'event.successfulAuthorizationAdjustmentEventDetails')
        );
    }
}
