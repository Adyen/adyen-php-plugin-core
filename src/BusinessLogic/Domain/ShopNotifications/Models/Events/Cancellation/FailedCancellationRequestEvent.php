<?php

namespace Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Cancellation;

use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Event;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Severity;
use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class FailedCancellationRequestEvent
 *
 * @package Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Cancellation
 */
class FailedCancellationRequestEvent extends Event
{
    /**
     * Message for failed cancellation request.
     */
    private const MESSAGE = 'Cancellation request failed.';

    /**
     * Details for failed cancellation request.
     */
    private const DETAILS = 'Cancellation request failed.';

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
            new TranslatableLabel(self::MESSAGE, 'event.failedCancellationRequestEventMessage'),
            new TranslatableLabel(self::DETAILS, 'event.failedCancellationRequestEventDetails')
        );
    }
}
