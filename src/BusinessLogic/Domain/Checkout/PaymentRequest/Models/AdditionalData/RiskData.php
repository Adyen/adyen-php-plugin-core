<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\AdditionalData;

/**
 * Class RiskData
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\AdditionalData
 */
class RiskData
{
    /**
     * @var BasketItem[]
     */
    private $basketItems;

    /**
     * @param BasketItem[] $basketItems
     */
    public function __construct(array $basketItems)
    {
        $this->basketItems = $basketItems;
    }

    /**
     * @return BasketItem[]
     */
    public function getBasketItems(): array
    {
        return $this->basketItems;
    }
}
