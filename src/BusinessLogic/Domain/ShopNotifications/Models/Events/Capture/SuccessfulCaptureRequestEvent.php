<?php

namespace Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Capture;

use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Event;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Severity;
use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class SuccessfulCaptureRequestEvent
 *
 * @package Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events
 */
class SuccessfulCaptureRequestEvent extends Event
{
    /**
     * Message for capture request.
     */
    private const MESSAGE = 'Capture request has been sent to Adyen.';

    /**
     * Details for capture request.
     */
    private const DETAILS = 'Capture request has been sent to Adyen.';

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
            new TranslatableLabel(self::MESSAGE, 'event.successfulCaptureRequestEventMessage'),
            new TranslatableLabel(self::DETAILS, 'event.successfulCaptureRequestEventDetails')
        );
    }
}
