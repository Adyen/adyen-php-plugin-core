<?php

namespace Adyen\Core\Tests\BusinessLogic\Domain\Checkout;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PaymentMethodCode;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;

/**
 * Class PaymentMethodCodeTest
 *
 * @package Adyen\Core\Tests\BusinessLogic\Domain\Checkout
 */
class PaymentMethodCodeTest extends BaseTestCase
{
    public function testWebhookOrderCreationSupportedForPendingActionMethods(): void
    {
        self::assertTrue(PaymentMethodCode::bcmcMobile()->isWebhookOrderCreationSupported());
        self::assertTrue(PaymentMethodCode::blik()->isWebhookOrderCreationSupported());
        self::assertTrue(PaymentMethodCode::mbWay()->isWebhookOrderCreationSupported());
        self::assertTrue(PaymentMethodCode::swish()->isWebhookOrderCreationSupported());
        self::assertTrue(PaymentMethodCode::weChatPay()->isWebhookOrderCreationSupported());
    }

    public function testWebhookOrderCreationNotSupportedForOtherMethods(): void
    {
        self::assertFalse(PaymentMethodCode::scheme()->isWebhookOrderCreationSupported());
        self::assertFalse(PaymentMethodCode::payPal()->isWebhookOrderCreationSupported());
        self::assertFalse(PaymentMethodCode::ideal()->isWebhookOrderCreationSupported());
    }
}
