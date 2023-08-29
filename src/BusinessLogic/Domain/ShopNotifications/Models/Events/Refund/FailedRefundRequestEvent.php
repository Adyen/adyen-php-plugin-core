<?php

namespace Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Refund;

use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Event;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Severity;
use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class FailedRefundRequestEvent
 *
 * @package Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Refund
 */
class FailedRefundRequestEvent extends Event
{
    /**
     * Message for failed refund request.
     */
    private const MESSAGE = 'Refund request failed.';

    /**
     * Details for failed refund request.
     */
    private const DETAILS = 'Refund request failed.';

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
            new TranslatableLabel(self::MESSAGE, 'event.failedRefundRequestEventMessage'),
            new TranslatableLabel(self::DETAILS, 'event.failedRefundRequestEventDetails')
        );
    }
}
