<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models;

/**
 * Class ShopperName
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models
 */
class ShopperReference
{
    /**
     * @var string
     */
    private $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * Creates new shopper reference instance out of raw shopper reference data
     *
     * @param string $reference
     * @return ShopperReference
     */
    public static function parse(string $reference): ShopperReference
    {
        return new self(str_pad($reference, 3, 0, STR_PAD_LEFT));
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
