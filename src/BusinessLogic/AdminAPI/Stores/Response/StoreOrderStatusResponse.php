<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\Stores\Response;

use Adyen\Core\BusinessLogic\AdminAPI\Response\Response;
use Adyen\Core\BusinessLogic\Domain\Stores\Models\StoreOrderStatus;

/**
 * Class StoreOrderStatusResponse
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\Stores\Response
 */
class StoreOrderStatusResponse extends Response
{
    /**
     * @var StoreOrderStatus[]
     */
    private $orderStatuses;

    /**
     * @param array $orderStatuses
     */
    public function __construct(array $orderStatuses)
    {
        $this->orderStatuses = $orderStatuses;
    }

    /**
     * Transforms Order statuses to array representation.
     *
     * @return array
     */
    public function toArray(): array
    {
        $orderStatuses = [];

        foreach ($this->orderStatuses as $status) {
            $orderStatuses[] = $this->transformStoreOrderStatus($status);
        }

        return $orderStatuses;
    }

    /**
     * @param StoreOrderStatus $status
     *
     * @return array
     */
    private function transformStoreOrderStatus(StoreOrderStatus $status): array
    {
        return [
            'statusId' => $status->getStatusId(),
            'statusName' => $status->getStatusName()
        ];
    }
}
