<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\InvalidTaxRate;
use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class TaxRate
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount
 */
class TaxRate
{
    /**
     * @var float
     */
    private $taxRatePercentage;

    /**
     * @param float $taxRatePercentage
     * @throws InvalidTaxRate
     */
    public function __construct(float $taxRatePercentage)
    {
        if ($taxRatePercentage < 0 || $taxRatePercentage >= 100) {
            throw new InvalidTaxRate(
                new TranslatableLabel('Tax rate should be between 0 and 100 percents.', 'checkout.taxError'));
        }

        $this->taxRatePercentage = $taxRatePercentage;
    }

    /**
     * @return float
     */
    public function getRate(): float
    {
        return $this->taxRatePercentage;
    }

    public function __toString(): string
    {
        return number_format($this->taxRatePercentage, 6);
    }

}
