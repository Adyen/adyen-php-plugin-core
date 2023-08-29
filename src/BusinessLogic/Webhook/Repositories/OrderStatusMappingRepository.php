<?php

namespace Adyen\Core\BusinessLogic\Webhook\Repositories;

use Exception;

/**
 * Class OrderStatusMappingRepository
 *
 * @package Adyen\Core\BusinessLogic\Domain\OrderStatusMapping\Repositories
 */
interface OrderStatusMappingRepository
{
    /**
     * Returns OrderStatusMapping instance for current store context.
     *
     * @return array
     */
    public function getOrderStatusMapping(): array;

    /**
     * Insert/update OrderStatusMapping for current store context;
     *
     * @param array $orderStatusMapping
     *
     * @return void
     */
    public function setOrderStatusMapping(array $orderStatusMapping): void;

    /**
     * Deletes order status mappings.
     *
     * @return void
     *
     * @throws Exception
     */
    public function deleteOrderStatusMapping(): void;
}
