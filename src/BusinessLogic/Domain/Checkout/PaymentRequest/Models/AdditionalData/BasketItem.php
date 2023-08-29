<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\AdditionalData;

/**
 * Class BasketItem
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\AdditionalData
 */
class BasketItem
{
    /**
     * @var string
     */
    private $itemId;
    /**
     * @var string
     */
    private $brand;
    /**
     * @var string
     */
    private $amountPerItem;
    /**
     * @var string
     */
    private $category;
    /**
     * @var string
     */
    private $color;
    /**
     * @var string
     */
    private $currency;
    /**
     * @var string
     */
    private $manufacturer;
    /**
     * @var string
     */
    private $productTitle;
    /**
     * @var string
     */
    private $quantity;
    /**
     * @var string
     */
    private $receiverEmail;
    /**
     * @var string
     */
    private $size;
    /**
     * @var string
     */
    private $sku;
    /**
     * @var string
     */
    private $upc;

    /**
     * @param string $itemId
     * @param string $brand
     * @param string $amountPerItem
     * @param string $category
     * @param string $color
     * @param string $currency
     * @param string $manufacturer
     * @param string $productTitle
     * @param string $quantity
     * @param string $receiverEmail
     * @param string $size
     * @param string $sku
     * @param string $upc
     */
    public function __construct(
        string $itemId,
        string $brand,
        string $amountPerItem,
        string $category,
        string $color,
        string $currency,
        string $manufacturer,
        string $productTitle,
        string $quantity,
        string $receiverEmail,
        string $size,
        string $sku,
        string $upc
    )
    {
        $this->itemId = $itemId;
        $this->brand = $brand;
        $this->amountPerItem = $amountPerItem;
        $this->category = $category;
        $this->color = $color;
        $this->currency = $currency;
        $this->manufacturer = $manufacturer;
        $this->productTitle = $productTitle;
        $this->quantity = $quantity;
        $this->receiverEmail = $receiverEmail;
        $this->size = $size;
        $this->sku = $sku;
        $this->upc = $upc;
    }

    /**
     * @return string
     */
    public function getItemId(): string
    {
        return $this->itemId;
    }

    /**
     * @return string
     */
    public function getBrand(): string
    {
        return $this->brand;
    }

    /**
     * @return string
     */
    public function getAmountPerItem(): string
    {
        return $this->amountPerItem;
    }

    /**
     * @return string
     */
    public function getCategory(): string
    {
        return $this->category;
    }

    /**
     * @return string
     */
    public function getColor(): string
    {
        return $this->color;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @return string
     */
    public function getManufacturer(): string
    {
        return $this->manufacturer;
    }

    /**
     * @return string
     */
    public function getProductTitle(): string
    {
        return $this->productTitle;
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
    public function getReceiverEmail(): string
    {
        return $this->receiverEmail;
    }

    /**
     * @return string
     */
    public function getSize(): string
    {
        return $this->size;
    }

    /**
     * @return string
     */
    public function getSku(): string
    {
        return $this->sku;
    }

    /**
     * @return string
     */
    public function getUpc(): string
    {
        return $this->upc;
    }
}
