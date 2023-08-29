<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models;

/**
 * Class AuthenticationData
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models
 */
class AuthenticationData
{
    /**
     * @var string
     */
    private $nativeThreeDS;

    public function __construct(string $nativeThreeDS = 'preferred')
    {
        $this->nativeThreeDS = $nativeThreeDS;
    }

    /**
     * @return string
     */
    public function getNativeThreeDS(): string
    {
        return $this->nativeThreeDS;
    }
}
