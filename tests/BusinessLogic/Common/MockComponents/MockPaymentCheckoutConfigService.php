<?php

namespace Adyen\Core\Tests\BusinessLogic\Common\MockComponents;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Services\PaymentCheckoutConfigService;

/**
 * Class MockPaymentCheckoutConfigService.
 *
 * @package Adyen\Core\Tests\BusinessLogic\Common\MockComponents
 */
class MockPaymentCheckoutConfigService extends PaymentCheckoutConfigService
{
    /**
     * @var bool $enabledExpressCheckout
     */
    private $enabledExpressCheckout = false;

    /**
     * @param bool $isGuest
     *
     * @return bool
     */
    public function hasEnabledExpressCheckoutPaymentMethods(bool $isGuest = false): bool
    {
        return $this->enabledExpressCheckout;
    }

    /**
     * @param bool $enabledExpressCheckout
     *
     * @return void
     */
    public function setEnabledExpressCheckout(bool $enabledExpressCheckout): void
    {
        $this->enabledExpressCheckout = $enabledExpressCheckout;
    }
}
