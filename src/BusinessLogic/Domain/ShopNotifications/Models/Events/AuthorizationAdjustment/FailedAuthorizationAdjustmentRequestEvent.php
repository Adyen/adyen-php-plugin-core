<?php

namespace Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\AuthorizationAdjustment;

use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Event;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Severity;
use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class FailedAuthorizationAdjustmentRequestEvent.
 *
 * @package Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\AuthorizationAdjustment
 */
class FailedAuthorizationAdjustmentRequestEvent extends Event
{
    /**
     * Message for failed authorization adjustment request.
     */
    private const MESSAGE = 'Authorization adjustment request failed.';

    /**
     * Details for failed authorization adjustment request.
     */
    private const DETAILS = 'Authorization adjustment request failed.';

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
            new TranslatableLabel(self::MESSAGE, 'event.failedAuthorizationAdjustmentRequestMessage'),
            new TranslatableLabel(self::DETAILS, 'event.failedAuthorizationAdjustmentRequestDetails')
        );
    }
}
