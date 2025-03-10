<?php

namespace Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\PartialPayment;

use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Event;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Severity;
use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class FailedOrderClosedEvent
 *
 * @package Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\PartialPayment
 */
class FailedOrderClosedEvent extends Event
{
    /**
     * Message for successful order closed.
     */
    private const MESSAGE = 'Order close failed on Adyen.';

    /**
     * Details for successful order closed.
     */
    private const DETAILS = 'Order close failed on Adyen..';

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
            new TranslatableLabel(self::MESSAGE, 'event.failedOrderClosedEventMessage'),
            new TranslatableLabel(self::DETAILS, 'event.failedOrderClosedEventDetails')
        );
    }
}
