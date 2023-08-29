<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models;

/**
 * Class Installments
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models
 */
class Installments
{
    /**
     * @var int
     */
    private $value;
    /**
     * @var string
     */
    private $plan;

    /**
     * @param int $value
     * @param string $plan
     */
    public function __construct(int $value, string $plan = 'regular')
    {
        $this->value = $value;
        $this->plan = $plan;
    }

    /**
     * @return int
     */
    public function getValue(): int
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getPlan(): string
    {
        return $this->plan;
    }
}
