<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\AdditionalData;

/**
 * Class ItemDetailLine
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\AdditionalData
 */
class ItemDetailLine
{
    /**
     * @var string
     */
    private $description;
    /**
     * @var string
     */
    private $productCode;
    /**
     * @var string
     */
    private $quantity;
    /**
     * @var string
     */
    private $unitOfMeasure;
    /**
     * @var string
     */
    private $unitPrice;
    /**
     * @var string
     */
    private $discountAmount;
    /**
     * @var string
     */
    private $totalAmount;
    /**
     * @var string
     */
    private $commodityCode;

    /**
     * @param string $description
     * @param string $productCode
     * @param string $quantity
     * @param string $unitOfMeasure
     * @param string $unitPrice
     * @param string $discountAmount
     * @param string $totalAmount
     * @param string $commodityCode
     */
    public function __construct(
        string $description,
        string $productCode,
        string $quantity,
        string $unitOfMeasure,
        string $unitPrice,
        string $discountAmount,
        string $totalAmount,
        string $commodityCode
    )
    {
        $this->description = $description;
        $this->productCode = $productCode;
        $this->quantity = $quantity;
        $this->unitOfMeasure = $unitOfMeasure;
        $this->unitPrice = $unitPrice;
        $this->discountAmount = $discountAmount;
        $this->totalAmount = $totalAmount;
        $this->commodityCode = $commodityCode;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getProductCode(): string
    {
        return $this->productCode;
    }

    /**
     * @return string
     */
    public function getQuantity(): string
    {
        return $this->quantity;
    }

    /**
     * @return string
     */
    public function getUnitOfMeasure(): string
    {
        return $this->unitOfMeasure;
    }

    /**
     * @return string
     */
    public function getUnitPrice(): string
    {
        return $this->unitPrice;
    }

    /**
     * @return string
     */
    public function getDiscountAmount(): string
    {
        return $this->discountAmount;
    }

    /**
     * @return string
     */
    public function getTotalAmount(): string
    {
        return $this->totalAmount;
    }

    /**
     * @return string
     */
    public function getCommodityCode(): string
    {
        return $this->commodityCode;
    }
}
