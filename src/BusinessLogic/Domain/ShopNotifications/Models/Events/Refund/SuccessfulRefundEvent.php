<?php

namespace Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Refund;

use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Event;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Severity;
use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class SuccessfulRefundEvent
 *
 * @package Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events
 */
class SuccessfulRefundEvent extends Event
{
    /**
     * Message for successful refund.
     */
    private const MESSAGE = 'Payment has been refunded successfully on Adyen.';

    /**
     * Details for successful capture.
     */
    private const DETAILS = 'Payment has been refunded successfully on Adyen.';

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
            new TranslatableLabel(self::MESSAGE, 'event.successfulRefundEventMessage'),
            new TranslatableLabel(self::DETAILS, 'event.successfulRefundEventDetails')
        );
    }
}
