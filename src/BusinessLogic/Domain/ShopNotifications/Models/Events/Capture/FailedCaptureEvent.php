<?php

namespace Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Capture;

use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Event;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Severity;
use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class FailedCaptureEvent
 *
 * @package Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events
 */
class FailedCaptureEvent extends Event
{
    /**
     * Message for failed capture.
     */
    private const MESSAGE = 'Capture failed on Adyen.';

    /**
     * Details for failed capture.
     */
    private const DETAILS = 'Capture failed on Adyen.';

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
            new TranslatableLabel(self::MESSAGE, 'event.failedCaptureEventMessage'),
            new TranslatableLabel(self::DETAILS, 'event.failedCaptureEventDetails')
        );
    }
}
