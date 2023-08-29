<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\OrderMappings\Request;

use Adyen\Webhook\PaymentStates;

/**
 * Class OrderMappingsRequest
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\OrderMappings\Request
 */
class OrderMappingsRequest
{
    /**
     * @var array
     */
    private $orderStatusMap;

    /**
     * @param array $orderStatusMap
     */
    private function __construct(
        array $orderStatusMap
    ) {
        $this->orderStatusMap = $orderStatusMap;
    }

    /**
     * @param array $payload
     *
     * @return static
     */
    public static function parse(array $payload): self
    {
        return new self(
            [
                PaymentStates::STATE_IN_PROGRESS => $payload['inProgress'] ?? '',
                PaymentStates::STATE_PENDING => $payload['pending'] ?? '',
                PaymentStates::STATE_PAID => $payload['paid'] ?? '',
                PaymentStates::STATE_FAILED => $payload['failed'] ?? '',
                PaymentStates::STATE_REFUNDED => $payload['refunded'] ?? '',
                PaymentStates::STATE_CANCELLED => $payload['cancelled'] ?? '',
                PaymentStates::STATE_PARTIALLY_REFUNDED => $payload['partiallyRefunded'] ?? '',
                PaymentStates::STATE_NEW => $payload['new'] ?? '',
                PaymentStates::CHARGE_BACK => $payload['chargeBack'] ?? ''
            ]
        );
    }

    /**
     * @return array
     */
    public function getOrderStatusMap(): array
    {
        return $this->orderStatusMap;
    }
}
