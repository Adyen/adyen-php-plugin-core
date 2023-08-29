<?php

namespace Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Cancellation;

use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Event;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Severity;
use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class SuccessfulCancellationRequestEvent
 *
 * @package Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Cancellation
 */
class SuccessfulCancellationRequestEvent extends Event
{
    /**
     * Message for cancellation request.
     */
    private const MESSAGE = 'Cancellation request has been sent to Adyen.';

    /**
     * Details for cancellation request.
     */
    private const DETAILS = 'Cancellation request has been sent to Adyen.';

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
            new TranslatableLabel(self::MESSAGE, 'event.successfulCancellationRequestEventMessage'),
            new TranslatableLabel(self::DETAILS, 'event.successfulCancellationRequestEventDetails')
        );
    }
}
