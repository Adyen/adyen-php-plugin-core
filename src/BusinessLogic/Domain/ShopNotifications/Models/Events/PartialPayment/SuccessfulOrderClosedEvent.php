<?php

namespace Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\PartialPayment;

use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Event;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Severity;
use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class SuccessfulOrderClosedEvent
 *
 * @package Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\PartialPayment
 */
class SuccessfulOrderClosedEvent extends Event
{
    /**
     * Message for successful order closed.
     */
    private const MESSAGE = 'Order has been successfully closed on Adyen.';

    /**
     * Details for successful order closed.
     */
    private const DETAILS = 'Order has been successfully closed on Adyen.';

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
            new TranslatableLabel(self::MESSAGE, 'event.successfulOrderClosedEventMessage'),
            new TranslatableLabel(self::DETAILS, 'event.successfulOrderClosedEventDetails')
        );
    }
}
