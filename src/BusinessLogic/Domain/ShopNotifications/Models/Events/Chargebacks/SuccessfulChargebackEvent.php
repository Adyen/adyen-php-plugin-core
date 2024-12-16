<?php

namespace Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Chargebacks;

use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Event;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events\Severity;
use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

class SuccessfulChargebackEvent extends Event
{
    /**
     * Message for successful adjustment.
     */
    private const MESSAGE = 'Payment has been successfully charged back on Adyen.';

    /**
     * Details for failed adjustment.
     */
    private const DETAILS = 'Payment has been successfully charged back on Adyen.';

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
            new TranslatableLabel(self::MESSAGE, 'event.successfulChargebackMessage'),
            new TranslatableLabel(self::DETAILS, 'event.successfulChargebackDetails')
        );
    }
}