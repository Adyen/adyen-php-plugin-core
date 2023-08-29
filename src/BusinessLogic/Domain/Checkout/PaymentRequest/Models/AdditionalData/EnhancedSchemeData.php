<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\AdditionalData;

/**
 * Class EnhancedSchemeData
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\AdditionalData
 */
class EnhancedSchemeData
{
    /**
     * @var string
     */
    private $totalTaxAmount;
    /**
     * @var string
     */
    private $customerReference;
    /**
     * @var string
     */
    private $freightAmount;
    /**
     * @var string
     */
    private $shipFromPostalCode;
    /**
     * @var string
     */
    private $orderDate;
    /**
     * @var string
     */
    private $dutyAmount;
    /**
     * @var string
     */
    private $destinationStateProvinceCode;
    /**
     * @var string
     */
    private $destinationCountryCode;
    /**
     * @var string
     */
    private $destinationPostalCode;
    /**
     * @var ItemDetailLine[]
     */
    private $itemDetailLines;

    /**
     * @param string $totalTaxAmount
     * @param string $customerReference
     * @param string $freightAmount
     * @param string $shipFromPostalCode
     * @param string $orderDate
     * @param string $dutyAmount
     * @param string $destinationStateProvinceCode
     * @param string $destinationCountryCode
     * @param string $destinationPostalCode
     * @param ItemDetailLine[] $itemDetailLines
     */
    public function __construct(
        string $totalTaxAmount,
        string $customerReference,
        string $freightAmount,
        string $shipFromPostalCode,
        string $orderDate,
        string $dutyAmount,
        string $destinationStateProvinceCode,
        string $destinationCountryCode,
        string $destinationPostalCode,
        array $itemDetailLines
    )
    {
        $this->totalTaxAmount = $totalTaxAmount;
        $this->customerReference = $customerReference;
        $this->freightAmount = $freightAmount;
        $this->shipFromPostalCode = $shipFromPostalCode;
        $this->orderDate = $orderDate;
        $this->dutyAmount = $dutyAmount;
        $this->destinationStateProvinceCode = $destinationStateProvinceCode;
        $this->destinationCountryCode = $destinationCountryCode;
        $this->destinationPostalCode = $destinationPostalCode;
        $this->itemDetailLines = $itemDetailLines;
    }

    /**
     * @return string
     */
    public function getTotalTaxAmount(): string
    {
        return $this->totalTaxAmount;
    }

    /**
     * @return string
     */
    public function getCustomerReference(): string
    {
        return $this->customerReference;
    }

    /**
     * @return string
     */
    public function getFreightAmount(): string
    {
        return $this->freightAmount;
    }

    /**
     * @return string
     */
    public function getShipFromPostalCode(): string
    {
        return $this->shipFromPostalCode;
    }

    /**
     * @return string
     */
    public function getOrderDate(): string
    {
        return $this->orderDate;
    }

    /**
     * @return string
     */
    public function getDutyAmount(): string
    {
        return $this->dutyAmount;
    }

    /**
     * @return string
     */
    public function getDestinationStateProvinceCode(): string
    {
        return $this->destinationStateProvinceCode;
    }

    /**
     * @return string
     */
    public function getDestinationCountryCode(): string
    {
        return $this->destinationCountryCode;
    }

    /**
     * @return string
     */
    public function getDestinationPostalCode(): string
    {
        return $this->destinationPostalCode;
    }

    /**
     * @return ItemDetailLine[]
     */
    public function getItemDetailLines(): array
    {
        return $this->itemDetailLines;
    }
}
