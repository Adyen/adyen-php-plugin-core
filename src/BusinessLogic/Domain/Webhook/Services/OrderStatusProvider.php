<?php

namespace Adyen\Core\BusinessLogic\Domain\Webhook\Services;

use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Models\TransactionHistory;
use Adyen\Core\BusinessLogic\Domain\Webhook\Models\Webhook;

/**
 * Interface OrderStatusProvider
 *
 * @package Adyen\Core\BusinessLogic\Domain\Webhook\Services
 */
interface OrderStatusProvider
{
    /**
     * Returns mapped order status id for payment state provided as first parameter.
     *
     * @param string $state
     *
     * @return string
     */
    public function getOrderStatus(string $state): string;

    /**
     * @param Webhook $webhook
     * @param TransactionHistory $transactionHistory
     *
     * @return string|null
     */
    public function getNewPaymentState(Webhook $webhook, TransactionHistory $transactionHistory): ?string;
}
