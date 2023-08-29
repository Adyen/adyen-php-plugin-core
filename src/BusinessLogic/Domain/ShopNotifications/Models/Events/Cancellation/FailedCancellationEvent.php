<?php

namespace Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Cancellation;

use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Event;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Severity;
use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class FailedCancellationEvent
 *
 * @package Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events
 */
class FailedCancellationEvent extends Event
{
    /**
     * Message for failed cancellation.
     */
    private const MESSAGE = 'Cancellation failed on Adyen.';

    /**
     * Details for failed cancellation.
     */
    private const DETAILS = 'Cancellation failed on Adyen.';

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
            new TranslatableLabel(self::MESSAGE, 'event.failedCancellationEventMessage'),
            new TranslatableLabel(self::DETAILS, 'event.failedCancellationEventDetails')
        );
    }
}
