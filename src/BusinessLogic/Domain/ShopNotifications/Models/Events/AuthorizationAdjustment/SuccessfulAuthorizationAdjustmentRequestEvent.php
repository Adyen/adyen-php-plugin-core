<?php

namespace Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\AuthorizationAdjustment;

use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Severity;
use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Event;

/**
 * Class SuccessfulAuthorizationAdjustmentRequestEvent.
 *
 * @package Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\AuthorizationAdjustment
 */
class SuccessfulAuthorizationAdjustmentRequestEvent extends Event
{
    /**
     * Message for successful authorization adjustment request.
     */
    private const MESSAGE = 'Authorization adjustment request has been sent successfully to Adyen.';

    /**
     * Details for successful authorization adjustment request.
     */
    private const DETAILS = 'Authorization adjustment request has been sent successfully to Adyen.';

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
