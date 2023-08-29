<?php

namespace Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Authorization;

use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Event;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Severity;
use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class SuccessfulPaymentAuthorizationEvent
 *
 * @package Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events
 */
class SuccessfulPaymentAuthorizationEvent extends Event
{
    /**
     * Message for successful payment authorization.
     */
    private const MESSAGE = 'Payment has been authorized successfully.';

    /**
     * Details for successful payment authorization.
     */
    private const DETAILS = 'Payment has been authorized by Adyen.';

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
            new TranslatableLabel(self::MESSAGE, 'event.successfulPaymentAuthorizationEventMessage'),
            new TranslatableLabel(self::DETAILS, 'event.successfulPaymentAuthorizationEventDetails')
        );
    }
}
