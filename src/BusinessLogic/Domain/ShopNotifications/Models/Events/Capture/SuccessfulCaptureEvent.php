<?php

namespace Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Capture;

use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Event;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Severity;
use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class SuccessfulCaptureEvent
 *
 * @package Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events
 */
class SuccessfulCaptureEvent extends Event
{
    /**
     * Message for successful capture.
     */
    private const MESSAGE = 'Payment has been captured successfully on Adyen.';

    /**
     * Details for successful capture.
     */
    private const DETAILS = 'Payment has been captured successfully on Adyen.';

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
            new TranslatableLabel(self::MESSAGE, 'event.successfulCaptureEventMessage'),
            new TranslatableLabel(self::DETAILS, 'event.successfulCaptureEventDetails')
        );
    }
}
