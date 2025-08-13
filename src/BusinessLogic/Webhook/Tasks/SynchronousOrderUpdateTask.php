<?php

namespace Adyen\Core\BusinessLogic\Webhook\Tasks;

use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Adyen\Core\BusinessLogic\Webhook\Exceptions\OrderNotFoundException;
use Exception;

/**
 * Class SynchronousOrderUpdateTask.
 *
 * @package Adyen\Core\BusinessLogic\Webhook\Tasks
 */
class SynchronousOrderUpdateTask extends OrderUpdateTask
{
    /**
     * Returns true if order is created in shop system. If it is not created sleep for 2 seconds and check again.
     * Repeat this checking for 5 times.
     *
     * @param int $retryCount
     *
     * @return bool
     *
     * @throws Exception
     */
    protected function checkIfOrderExists(int $retryCount = 0): bool
    {
        try {
            $order = $this->getOrderService()->orderExists($this->webhook->getMerchantReference());
        } catch (Exception $exception) {
            $order = false;
        }

        if (!$order) {
            throw new OrderNotFoundException(
                new TranslatableLabel(
                    'Order with ID: ' . $this->webhook->getMerchantReference() . ' not found.', 'order.notFound'
                )
            );
        }

        return true;
    }
}
