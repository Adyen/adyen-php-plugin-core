<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\OrderMappings\Response;

use Adyen\Core\BusinessLogic\AdminAPI\Response\Response;
use Adyen\Webhook\PaymentStates;

/**
 * Class OrderMappingsGetResponse
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\OrderMappings\Response
 */
class OrderMappingsGetResponse extends Response
{
    /**
     * @var array
     */
    private $orderStatusMapping;

    /**
     * @param array $orderStatusMapping
     */
    public function __construct(array $orderStatusMapping)
    {
        $this->orderStatusMapping = $orderStatusMapping;
    }

    /**
     * Returns array representation of OrderStatusMap.
     *
     * @return array Array representation of order status map.
     */
    public function toArray(): array
    {
        return $this->transformOrderStatusMap();
    }

    /**
     * @return array
     */
    private function transformOrderStatusMap(): array
    {
        return [
            'inProgress' => (string)$this->orderStatusMapping[PaymentStates::STATE_IN_PROGRESS],
            'pending' => (string)$this->orderStatusMapping[PaymentStates::STATE_PENDING],
            'paid' => (string)$this->orderStatusMapping[PaymentStates::STATE_PAID],
            'failed' => (string)$this->orderStatusMapping[PaymentStates::STATE_FAILED],
            'refunded' => (string)$this->orderStatusMapping[PaymentStates::STATE_REFUNDED],
            'cancelled' => (string)$this->orderStatusMapping[PaymentStates::STATE_CANCELLED],
            'partiallyRefunded' => (string)$this->orderStatusMapping[PaymentStates::STATE_PARTIALLY_REFUNDED],
            'new' => (string)$this->orderStatusMapping[PaymentStates::STATE_NEW],
            'chargeBack' => (string)$this->orderStatusMapping[PaymentStates::CHARGE_BACK]
        ];
    }
}
