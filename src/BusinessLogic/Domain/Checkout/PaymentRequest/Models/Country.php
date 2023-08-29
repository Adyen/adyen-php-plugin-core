<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models;


/**
 * Class Country
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models
 */
class Country
{
    /**
     * @var string
     */
    private $country;

    /**
     * @param string $country
     */
    private function __construct(string $country)
    {
        $this->country = $country;
    }

    /**
     * Country iso code should be in two letters format
     *
     * @param string $isoCode
     *
     * @return Country
     */
    public static function fromIsoCode(string $isoCode): Country
    {
        return new self(strtoupper($isoCode));
    }

    /**
     * @return string
     */
    public function getIsoCode(): string
    {
        return $this->country;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->country;
    }
}
