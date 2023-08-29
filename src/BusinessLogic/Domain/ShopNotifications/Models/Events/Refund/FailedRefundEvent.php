<?php

namespace Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Refund;

use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Event;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Severity;
use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class FailedRefundEvent
 *
 * @package Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events
 */
class FailedRefundEvent extends Event
{
    /**
     * Message for failed refund.
     */
    private const MESSAGE = 'Refund failed on Adyen.';

    /**
     * Details for failed refund.
     */
    private const DETAILS = 'Refund failed on Adyen.';

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
            new TranslatableLabel(self::MESSAGE, 'event.failedRefundEventMessage'),
            new TranslatableLabel(self::DETAILS, 'event.failedRefundEventDetails')
        );
    }
}
