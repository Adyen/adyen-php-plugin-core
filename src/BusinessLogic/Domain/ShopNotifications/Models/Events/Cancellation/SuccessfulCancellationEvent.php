<?php

namespace Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Cancellation;

use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Event;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Severity;
use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class SuccessfulCancellationEvent
 *
 * @package Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events
 */
class SuccessfulCancellationEvent extends Event
{
    /**
     * Message for successful cancellation.
     */
    private const MESSAGE = 'Payment has been cancelled on Adyen.';

    /**
     * Details for failed cancellation.
     */
    private const DETAILS = 'Payment has been cancelled on Adyen.';

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
            new TranslatableLabel(self::MESSAGE, 'event.successfulCancellationEventMessage'),
            new TranslatableLabel(self::DETAILS, 'event.successfulCancellationEventDetails')
        );
    }
}
