<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models;

/**
 * Class LineItem
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models
 */
class LineItem
{
    /**
     * @var string
     */
    private $id;
    /**
     * @var int
     */
    private $amountExcludingTax;
    /**
     * @var int
     */
    private $amountIncludingTax;
    /**
     * @var int
     */
    private $taxAmount;
    /**
     * @var int
     */
    private $taxPercentage;
    /**
     * @var string
     */
    private $description;
    /**
     * @var string
     */
    private $imageUrl;
    /**
     * @var string
     */
    private $itemCategory;
    /**
     * @var int
     */
    private $quantity;

    /**
     * @param string $id
     * @param int $amountExcludingTax
     * @param int $amountIncludingTax
     * @param int $taxAmount
     * @param int $taxPercentage
     * @param string $description
     * @param string $imageUrl
     * @param string $itemCategory
     * @param int $quantity
     */
    public function __construct(
        string $id,
        int $amountExcludingTax,
        int $amountIncludingTax,
        int $taxAmount,
        int $taxPercentage,
        string $description,
        string $imageUrl,
        string $itemCategory,
        int $quantity
    )
    {
        $this->id = $id;
        $this->amountExcludingTax = $amountExcludingTax;
        $this->amountIncludingTax = $amountIncludingTax;
        $this->taxAmount = $taxAmount;
        $this->taxPercentage = $taxPercentage;
        $this->description = $description;
        $this->imageUrl = $imageUrl;
        $this->itemCategory = $itemCategory;
        $this->quantity = $quantity;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getAmountExcludingTax(): int
    {
        return $this->amountExcludingTax;
    }

    /**
     * @return int
     */
    public function getAmountIncludingTax(): int
    {
        return $this->amountIncludingTax;
    }

    /**
     * @return int
     */
    public function getTaxAmount(): int
    {
        return $this->taxAmount;
    }

    /**
     * @return int
     */
    public function getTaxPercentage(): int
    {
        return $this->taxPercentage;
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
    public function getImageUrl(): string
    {
        return $this->imageUrl;
    }

    /**
     * @return string
     */
    public function getItemCategory(): string
    {
        return $this->itemCategory;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }
}
