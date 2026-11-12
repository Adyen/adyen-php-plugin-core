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
    /**
     * @dataProvider webhookPaymentMethodCodes
     *
     * @param string $code
     * @param bool $expected
     *
     * @return void
     */
    public function testWebhookOrderCreationSupport(string $code, bool $expected): void
    {
        self::assertSame($expected, PaymentMethodCode::isWebhookOrderCreationSupported($code));
    }

    /**
     * Raw paymentMethod values as Adyen sends them in notifications, including codes parse() rejects.
     *
     * @return array
     */
    public function webhookPaymentMethodCodes(): array
    {
        return [
            'bcmc_mobile' => ['bcmc_mobile', true],
            'blik' => ['blik', true],
            'mbway' => ['mbway', true],
            'swish' => ['swish', true],
            'wechatpayQR' => ['wechatpayQR', true],
            'wechatpay' => ['wechatpay', true],
            'bcmc_mobile_QR variant' => ['bcmc_mobile_QR', true],
            'bcmc_mobile_app variant' => ['bcmc_mobile_app', true],
            'wechatpay_pos variant' => ['wechatpay_pos', true],
            'same prefix without underscore' => ['blikx', false],
            'wechatpayWeb redirect' => ['wechatpayWeb', false],
            'scheme' => ['scheme', false],
            'bcmc' => ['bcmc', false],
            'paypal' => ['paypal', false],
            'ideal' => ['ideal', false],
            'twint' => ['twint', false],
            'mobilepay' => ['mobilepay', false],
            'vipps' => ['vipps', false],
            'card brand visa' => ['visa', false],
            'case differs' => ['BLIK', false],
            'empty' => ['', false],
        ];
    }
}
