<?php

namespace Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Refund;

use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Event;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Severity;
use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class SuccessfulRefundRequestEvent
 *
 * @package Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Refund
 */
class SuccessfulRefundRequestEvent extends Event
{
    /**
     * Message for refund request.
     */
    private const MESSAGE = 'Refund request has been sent to Adyen.';

    /**
     * Details for refund request.
     */
    private const DETAILS = 'Refund request has been sent to Adyen.';

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
            new TranslatableLabel(self::MESSAGE, 'event.successfulRefundRequestEventMessage'),
            new TranslatableLabel(self::DETAILS, 'event.successfulRefundRequestEventDetails')
        );
    }
}
