<?php

namespace Adyen\Core\BusinessLogic\Domain\PartialPayments\Proxies;

use Adyen\Core\BusinessLogic\Domain\PartialPayments\Models\BalanceRequest;
use Adyen\Core\BusinessLogic\Domain\PartialPayments\Models\BalanceResult;
use Adyen\Core\BusinessLogic\Domain\PartialPayments\Models\OrderCancelRequest;
use Adyen\Core\BusinessLogic\Domain\PartialPayments\Models\OrderCancelResult;
use Adyen\Core\BusinessLogic\Domain\PartialPayments\Models\OrderCreateRequest;
use Adyen\Core\BusinessLogic\Domain\PartialPayments\Models\OrderCreateResult;

/**
 * Interface PartialPaymentProxy
 *
 * @package Adyen\Core\BusinessLogic\Domain\PartialPayments\Proxies
 */
interface PartialPaymentProxy
{
    /**
     * Get the balance of a gift card.
     *
     * @param BalanceRequest $balanceRequest
     *
     * @return BalanceResult
     */
    public function getBalance(BalanceRequest $balanceRequest): BalanceResult;

    /**
     * Create an order.
     *
     * @param OrderCreateRequest $orderCreateRequest
     *
     * @return OrderCreateResult
     */
    public function createOrder(OrderCreateRequest $orderCreateRequest): OrderCreateResult;

    /**
     * Cancel an order.
     *
     * @param OrderCancelRequest $orderCancelRequest
     *
     * @return OrderCancelResult
     */
    public function cancelOrder(OrderCancelRequest $orderCancelRequest): OrderCancelResult;
}
