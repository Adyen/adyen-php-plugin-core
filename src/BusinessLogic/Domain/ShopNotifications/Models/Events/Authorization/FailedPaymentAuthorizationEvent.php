<?php

namespace Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Authorization;

use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Event;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Severity;
use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class FailedPaymentAuthorizationEvent
 *
 * @package Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events
 */
class FailedPaymentAuthorizationEvent extends Event
{
    /**
     * Message for failed payment authorization.
     */
    private const MESSAGE = 'Payment authorization failed.';

    /**
     * Details for failed payment authorization.
     */
    private const DETAILS = 'Payment authorization failed on Adyen.';

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
            new TranslatableLabel(self::MESSAGE, 'event.failedPaymentAuthorizationMessage'),
            new TranslatableLabel(self::DETAILS, 'event.failedPaymentAuthorizationDetails')
        );
    }
}
