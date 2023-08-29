<?php

namespace Adyen\Core\BusinessLogic\Domain\Stores\Models;

use Adyen\Core\BusinessLogic\Domain\Stores\Exceptions\InvalidShopOrderDataException;
use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class StoreOrderStatus
 *
 * @package Adyen\Core\BusinessLogic\Domain\OrderStatusMapping\Models
 */
class StoreOrderStatus
{
    /**
     * @var string
     */
    private $statusId;

    /**
     * @var string
     */
    private $statusName;

    /**
     * @param string $statusId
     * @param string $statusName
     *
     * @throws InvalidShopOrderDataException
     */
    public function __construct(string $statusId, string $statusName)
    {
        $this->validate($statusId, $statusName);

        $this->statusId = $statusId;
        $this->statusName = $statusName;
    }

    /**
     * @return string
     */
    public function getStatusId(): string
    {
        return $this->statusId;
    }

    /**
     * @return string
     */
    public function getStatusName(): string
    {
        return $this->statusName;
    }

    /**
     * Validates input parameters.
     *
     * @param string $statusId
     * @param string $statusName
     *
     * @return void
     *
     * @throws InvalidShopOrderDataException
     */
    private function validate(string $statusId, string $statusName): void
    {
        if (empty($statusId) || empty($statusName)) {
            throw new InvalidShopOrderDataException(
                new TranslatableLabel('Status Id and status name are invalid.', 'stores.invalidIdAndStatus'));
        }
    }
}
