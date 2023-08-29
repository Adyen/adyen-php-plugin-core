<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models;

/**
 * Class DeliveryAddress
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models
 */
class DeliveryAddress
{
    /**
     * @var string
     */
    private $city;
    /**
     * @var string
     */
    private $country;
    /**
     * @var string
     */
    private $houseNumberOrName;
    /**
     * @var string
     */
    private $postalCode;
    /**
     * @var string
     */
    private $stateOrProvince;
    /**
     * @var string
     */
    private $street;

    /**
     * @param string $city
     * @param string $country
     * @param string $houseNumberOrName
     * @param string $postalCode
     * @param string $stateOrProvince
     * @param string $street
     */
    public function __construct(
        string $city,
        string $country,
        string $houseNumberOrName,
        string $postalCode,
        string $stateOrProvince,
        string $street
    )
    {
        $this->city = $city;
        $this->country = $country;
        $this->houseNumberOrName = $houseNumberOrName;
        $this->postalCode = $postalCode;
        $this->stateOrProvince = $stateOrProvince;
        $this->street = $street;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @return string
     */
    public function getCountry(): string
    {
        return $this->country;
    }

    /**
     * @return string
     */
    public function getHouseNumberOrName(): string
    {
        return $this->houseNumberOrName;
    }

    /**
     * @return string
     */
    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    /**
     * @return string
     */
    public function getStateOrProvince(): string
    {
        return $this->stateOrProvince;
    }

    /**
     * @return string
     */
    public function getStreet(): string
    {
        return $this->street;
    }
}
