<?php

namespace Adyen\Core\BusinessLogic\Domain\Payment\Enum;

/**
 * Class PaymentMethodType
 *
 * @package Adyen\Core\BusinessLogic\Domain\Payment\Enum
 */
interface PaymentMethodType
{
    public const PAYMENT_METHOD_TYPES = [
        'scheme' => 'creditOrDebitCard',
        'oney' => 'buyNowPayLater',
        'facilypay_3x' => 'buyNowPayLater',
        'facilypay_4x' => 'buyNowPayLater',
        'facilypay_6x' => 'buyNowPayLater',
        'facilypay_10x' => 'buyNowPayLater',
        'facilypay_12x' => 'buyNowPayLater',
        'afterpay_default' => 'buyNowPayLater',
        'clearpay' => 'buyNowPayLater',
        'klarna' => 'buyNowPayLater',
        'klarna_account' => 'buyNowPayLater',
        'multibanco' => 'cashOrAtm',
        'ach' => 'directDebit',
        'sepadirectdebit' => 'directDebit',
        'directdebit_GB' => 'directDebit',
        'blik' => 'onlinePayments',
        'eps' => 'onlinePayments',
        'giropay' => 'onlinePayments',
        'ideal' => 'onlinePayments',
        'klarna_paynow' => 'onlinePayments',
        'mbway' => 'onlinePayments',
        'mobilepay' => 'onlinePayments',
        'ebanking_FI' => 'onlinePayments',
        'billdesk_online' => 'onlinePayments',
        'onlineBanking_PL' => 'onlinePayments',
        'molpay_ebanking_TH' => 'onlinePayments',
        'directEbanking' => 'onlinePayments',
        'trustly' => 'onlinePayments',
        'applepay' => 'wallet',
        'amazonpay' => 'wallet',
        'alipay' => 'wallet',
        'bcmc_mobile' => 'wallet',
        'googlepay' => 'wallet',
        'paywithgoogle' => 'wallet',
        'gcash' => 'wallet',
        'momo_wallet' => 'wallet',
        'paypal' => 'wallet',
        'swish' => 'wallet',
        'vipps' => 'wallet',
        'zip' => 'wallet',
        'wechatpayQR' => 'wallet',
        'wechatpay' => 'wallet',
        'giftcard' => 'prepaidAndGiftCard',
        'paysafecard' => 'prepaidAndGiftCard',
        'twint' => 'mobile',
    ];
}
