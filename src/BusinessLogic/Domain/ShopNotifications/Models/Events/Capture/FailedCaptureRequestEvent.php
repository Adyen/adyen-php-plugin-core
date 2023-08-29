<?php

namespace Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Capture;

use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Event;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Severity;
use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class FailedCaptureRequestEvent
 *
 * @package Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Capture
 */
class FailedCaptureRequestEvent extends Event
{
    /**
     * Message for capture request.
     */
    private const MESSAGE = 'Capture request failed.';

    /**
     * Details for capture request.
     */
    private const DETAILS = 'Capture request failed.';

    /**
     * @param string $orderId
     * @param string $paymentMethod
     */
    public function __construct(string $orderId, string $paymentMethod)
    {
        parent::__construct(
            $orderId,
            $paymentMethod,
            Severity::error(),
            new TranslatableLabel(self::MESSAGE, 'event.failedCaptureRequestEventMessage'),
            new TranslatableLabel(self::DETAILS, 'event.failedCaptureRequestEventDetails')
        );
    }
}
