<?php

namespace Adyen\Core\BusinessLogic\Domain\PartialPayments\Models;

/**
 * Class OrderCancelRequest
 *
 * @package Adyen\Core\BusinessLogic\Domain\PartialPayments\Models
 */
class OrderCancelRequest
{
    /**
     * @var string
     */
    private $merchantAccount;
    /**
     * @var Order
     */
    private $order;

    /**
     * @param string $merchantAccount
     * @param Order $order
     */
    public function __construct(string $merchantAccount, Order $order)
    {
        $this->merchantAccount = $merchantAccount;
        $this->order = $order;
    }

    public function getMerchantAccount(): string
    {
        return $this->merchantAccount;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }
}
