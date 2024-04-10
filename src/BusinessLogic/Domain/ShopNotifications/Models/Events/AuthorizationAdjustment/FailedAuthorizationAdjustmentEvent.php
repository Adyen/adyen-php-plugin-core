<?php

namespace Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\AuthorizationAdjustment;

use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Event;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Severity;
use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class FailedAuthorizationAdjustmentEvent.
 *
 * @package Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\AuthorizationAdjustment
 */
class FailedAuthorizationAdjustmentEvent extends Event
{
    /**
     * Message for failed Authorization adjustment.
     */
    private const MESSAGE = 'Authorization adjustment failed on Adyen.';

    /**
     * Details for failed Authorization adjustment.
     */
    private const DETAILS = 'Authorization adjustment failed on Adyen.';

    /**
     * @param string $orderId
     * @param string $paymentMethod
     */
    public function __construct(string $orderId, string $paymentMethod)
    {
        parent::__construct(
            $orderId,
            $paymentMethod,
            Severity::warning(),
            new TranslatableLabel(self::MESSAGE, 'event.failedAuthorizationAdjustmentEventMessage'),
            new TranslatableLabel(self::DETAILS, 'event.failedAuthorizationAdjustmentEventDetails')
        );
    }
}
