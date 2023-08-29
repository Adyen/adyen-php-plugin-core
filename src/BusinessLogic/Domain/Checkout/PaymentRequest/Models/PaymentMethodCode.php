<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\InvalidPaymentMethodCodeException;
use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class PaymentMethodType
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models
 */
class PaymentMethodCode
{
    public const SUPPORTED_PAYMENT_METHODS = [
        self::SCHEME,
        self::AMERICAN_EXPRESS,
        self::CARNET,
        self::CARTES_BANCAIRES,
        self::CHINA_UNIONPAY,
        self::DINERS,
        self::DISCOVER,
        self::ELECTRON,
        self::ELO,
        self::HIPERCARD,
        self::JCB,
        self::MAESTRO,
        self::MASTERCARD,
        self::TROY,
        self::VISA,
        self::ONEY_3X,
        self::ONEY_4X,
        self::ONEY_6X,
        self::ONEY_10X,
        self::ONEY_12X,
        self::AFTERPAYTOUCH,
        self::CLEARPAY,
        self::KLARNA,
        self::KLARNA_ACCOUNT,
        self::MULTIBANCO,
        self::ACH,
        self::SEPA,
        self::DIRECTDEBIT_GB,
        self::BLIK,
        self::EPS,
        self::GIROPAY,
        self::IDEAL,
        self::KLARNA_PAYNOW,
        self::MBWAY,
        self::MOBILEPAY,
        self::EBANKING_FI,
        self::ONLINEBANKING_IN,
        self::ONLINEBANKING_PL,
        self::MOLPAY_EBANKING_TH,
        self::DIRECT_EBANKING,
        self::TRUSTLY,
        self::APPLEPAY,
        self::AMAZONPAY,
        self::ALIPAY,
        self::BCMC_MOBILE,
        self::GOOGLEPAY,
        self::PAY_WITH_GOOGLE,
        self::GCASH,
        self::MOMO_WALLET,
        self::PAYPAL,
        self::SWISH,
        self::VIPPS,
        self::ZIP,
        self::WECHATPAYQR,
        //<editor-fold desc="Gift cards" defaultstate="collapsed">
        self::GIFTCARD,
        self::AURIGA,
        self::BABYGIFTCARD,
        self::BLOEMENGIFTCARD,
        self::CASHCOMGIFTCARD,
        self::EAGLEEYE_VOUCHER,
        self::ENTERCARD,
        self::EXPERTGIFTCARD,
        self::FASHIONCHEQUE,
        self::FIJNCADEAU,
        self::VALUELINK,
        self::FLEUROPBLOEMENBON,
        self::FONQGIFTCARD,
        self::GALLGALL,
        self::GIVEX,
        self::HALLMARKCARD,
        self::IGIVE,
        self::IKANO,
        self::KADOWERELD,
        self::KIDSCADEAU,
        self::KINDPAS,
        self::LEISURECARD,
        self::NATIONALEBIOSCOOPBON,
        self::NETSCARD,
        self::OBERTHUR,
        self::PATHEGIFTCARD,
        self::PAYEX,
        self::PODIUMCARD,
        self::RESURSGIFTCARD,
        self::ROTTERDAMPAS,
        self::GENERICGIFTCARD,
        self::SCHOOLSPULLENPAS,
        self::SPARNORD,
        self::SPAREBANK,
        self::SVS,
        self::UNIVERSALGIFTCARD,
        self::VVVCADEAUBON,
        self::VVVGIFTCARD,
        self::WEBSHOPGIFTCARD,
        self::WINKELCHEQUE,
        self::WINTERKLEDINGPAS,
        self::XPONCARD,
        self::YOURGIFT,
        self::PROSODIE_ILLICADO,
        //</editor-fold>
        self::PAYSAFECARD,
        self::TWINT,
    ];

    public const CAPTURE_SUPPORTED = [
        self::SCHEME,
        self::AMERICAN_EXPRESS,
        self::CARNET,
        self::CARTES_BANCAIRES,
        self::CHINA_UNIONPAY,
        self::DINERS,
        self::DISCOVER,
        self::ELECTRON,
        self::ELO,
        self::HIPERCARD,
        self::JCB,
        self::MAESTRO,
        self::MASTERCARD,
        self::TROY,
        self::VISA,
        self::ONEY,
        self::ONEY_3X,
        self::ONEY_4X,
        self::ONEY_6X,
        self::ONEY_10X,
        self::ONEY_12X,
        self::AFTERPAYTOUCH,
        self::CLEARPAY,
        self::KLARNA,
        self::KLARNA_ACCOUNT,
        self::ACH,
        self::SEPA,
        self::DIRECTDEBIT_GB,
        self::KLARNA_PAYNOW,
        self::MOBILEPAY,
        self::APPLEPAY,
        self::AMAZONPAY,
        self::GOOGLEPAY,
        self::PAY_WITH_GOOGLE,
        self::PAYPAL,
        self::VIPPS,
        self::ZIP,
        //<editor-fold desc="Gift cards" defaultstate="collapsed">
        self::GIFTCARD,
        self::AURIGA,
        self::BABYGIFTCARD,
        self::BLOEMENGIFTCARD,
        self::CASHCOMGIFTCARD,
        self::EAGLEEYE_VOUCHER,
        self::ENTERCARD,
        self::EXPERTGIFTCARD,
        self::FASHIONCHEQUE,
        self::FIJNCADEAU,
        self::VALUELINK,
        self::FLEUROPBLOEMENBON,
        self::FONQGIFTCARD,
        self::GALLGALL,
        self::GIVEX,
        self::HALLMARKCARD,
        self::IGIVE,
        self::IKANO,
        self::KADOWERELD,
        self::KIDSCADEAU,
        self::KINDPAS,
        self::LEISURECARD,
        self::NATIONALEBIOSCOOPBON,
        self::NETSCARD,
        self::OBERTHUR,
        self::PATHEGIFTCARD,
        self::PAYEX,
        self::PODIUMCARD,
        self::RESURSGIFTCARD,
        self::ROTTERDAMPAS,
        self::GENERICGIFTCARD,
        self::SCHOOLSPULLENPAS,
        self::SPARNORD,
        self::SPAREBANK,
        self::SVS,
        self::UNIVERSALGIFTCARD,
        self::VVVCADEAUBON,
        self::VVVGIFTCARD,
        self::WEBSHOPGIFTCARD,
        self::WINKELCHEQUE,
        self::WINTERKLEDINGPAS,
        self::XPONCARD,
        self::YOURGIFT,
        self::PROSODIE_ILLICADO,
        //</editor-fold>
        self::TWINT,
    ];

    public const PARTIAL_CAPTURE_SUPPORTED = [
        self::SCHEME,
        self::AMERICAN_EXPRESS,
        self::CARTES_BANCAIRES,
        self::CHINA_UNIONPAY,
        self::DINERS,
        self::DISCOVER,
        self::ELECTRON,
        self::ELO,
        self::HIPERCARD,
        self::JCB,
        self::MAESTRO,
        self::MASTERCARD,
        self::VISA,
        self::AFTERPAYTOUCH,
        self::CLEARPAY,
        self::KLARNA,
        self::KLARNA_ACCOUNT,
        self::SEPA,
        self::DIRECTDEBIT_GB,
        self::KLARNA_PAYNOW,
        self::MOBILEPAY,
        self::TRUSTLY,
        self::APPLEPAY,
        self::GOOGLEPAY,
        self::PAY_WITH_GOOGLE,
        self::PAYPAL,
        self::VIPPS,
        self::ZIP,
        self::TWINT,
    ];

    public const REFUND_SUPPORTED = [
        self::SCHEME,
        self::AMERICAN_EXPRESS,
        self::BANCONTACT_CARD,
        self::CARTES_BANCAIRES,
        self::CHINA_UNIONPAY,
        self::DINERS,
        self::DISCOVER,
        self::ELECTRON,
        self::ELO,
        self::HIPERCARD,
        self::JCB,
        self::MAESTRO,
        self::TROY,
        self::MASTERCARD,
        self::VISA,
        self::ONEY,
        self::ONEY_3X,
        self::ONEY_4X,
        self::ONEY_6X,
        self::ONEY_10X,
        self::ONEY_12X,
        self::ACH,
        self::BCMC_MOBILE,
        self::DIRECTDEBIT_GB,
        self::SEPA,
        self::GOOGLEPAY,
        self::PAY_WITH_GOOGLE,
        self::AMAZONPAY,
        self::APPLEPAY,
        self::PAYPAL,
        self::ALIPAY,
        self::GCASH,
        self::MOMO_WALLET,
        self::SWISH,
        self::ZIP,
        self::WECHATPAYQR,
        self::VIPPS,
        self::AFTERPAYTOUCH,
        self::KLARNA,
        self::KLARNA_ACCOUNT,
        self::KLARNA_PAYNOW,
        self::CLEARPAY,
        self::IDEAL,
        self::MOBILEPAY,
        self::DIRECT_EBANKING,
        self::GIROPAY,
        self::TRUSTLY,
        self::ONLINEBANKING_PL,
        self::ONLINEBANKING_IN,
        self::BLIK,
        self::EPS,
        self::EBANKING_FI,
        self::MBWAY,
        //<editor-fold desc="Gift cards" defaultstate="collapsed">
        self::GIFTCARD,
        self::AURIGA,
        self::BABYGIFTCARD,
        self::BLOEMENGIFTCARD,
        self::CASHCOMGIFTCARD,
        self::EAGLEEYE_VOUCHER,
        self::ENTERCARD,
        self::EXPERTGIFTCARD,
        self::FASHIONCHEQUE,
        self::FIJNCADEAU,
        self::VALUELINK,
        self::FLEUROPBLOEMENBON,
        self::FONQGIFTCARD,
        self::GALLGALL,
        self::GIVEX,
        self::HALLMARKCARD,
        self::IGIVE,
        self::IKANO,
        self::KADOWERELD,
        self::KIDSCADEAU,
        self::KINDPAS,
        self::LEISURECARD,
        self::NATIONALEBIOSCOOPBON,
        self::NETSCARD,
        self::OBERTHUR,
        self::PATHEGIFTCARD,
        self::PAYEX,
        self::PODIUMCARD,
        self::RESURSGIFTCARD,
        self::ROTTERDAMPAS,
        self::GENERICGIFTCARD,
        self::SCHOOLSPULLENPAS,
        self::SPARNORD,
        self::SPAREBANK,
        self::SVS,
        self::UNIVERSALGIFTCARD,
        self::VVVCADEAUBON,
        self::VVVGIFTCARD,
        self::WEBSHOPGIFTCARD,
        self::WINKELCHEQUE,
        self::WINTERKLEDINGPAS,
        self::XPONCARD,
        self::YOURGIFT,
        self::PROSODIE_ILLICADO,
        //</editor-fold>
        self::PAYSAFECARD,
        self::TWINT
    ];

    public const PARTIAL_REFUND_SUPPORTED = [
        self::SCHEME,
        self::AMERICAN_EXPRESS,
        self::BANCONTACT_CARD,
        self::CARTES_BANCAIRES,
        self::CHINA_UNIONPAY,
        self::DINERS,
        self::DISCOVER,
        self::ELECTRON,
        self::ELO,
        self::HIPERCARD,
        self::JCB,
        self::MAESTRO,
        self::TROY,
        self::MASTERCARD,
        self::VISA,
        self::ONEY,
        self::ONEY_3X,
        self::ONEY_4X,
        self::ONEY_6X,
        self::ONEY_10X,
        self::ONEY_12X,
        self::ACH,
        self::BCMC_MOBILE,
        self::DIRECTDEBIT_GB,
        self::SEPA,
        self::GOOGLEPAY,
        self::PAY_WITH_GOOGLE,
        self::AMAZONPAY,
        self::APPLEPAY,
        self::PAYPAL,
        self::ALIPAY,
        self::GCASH,
        self::MOMO_WALLET,
        self::SWISH,
        self::ZIP,
        self::WECHATPAYQR,
        self::VIPPS,
        self::AFTERPAYTOUCH,
        self::KLARNA,
        self::KLARNA_ACCOUNT,
        self::KLARNA_PAYNOW,
        self::CLEARPAY,
        self::IDEAL,
        self::MOBILEPAY,
        self::DIRECT_EBANKING,
        self::GIROPAY,
        self::TRUSTLY,
        self::ONLINEBANKING_PL,
        self::BLIK,
        self::EPS,
        self::EBANKING_FI,
        self::MBWAY,
        //<editor-fold desc="Gift cards" defaultstate="collapsed">
        self::GIFTCARD,
        self::AURIGA,
        self::BABYGIFTCARD,
        self::BLOEMENGIFTCARD,
        self::CASHCOMGIFTCARD,
        self::EAGLEEYE_VOUCHER,
        self::ENTERCARD,
        self::EXPERTGIFTCARD,
        self::FASHIONCHEQUE,
        self::FIJNCADEAU,
        self::VALUELINK,
        self::FLEUROPBLOEMENBON,
        self::FONQGIFTCARD,
        self::GALLGALL,
        self::GIVEX,
        self::HALLMARKCARD,
        self::IGIVE,
        self::IKANO,
        self::KADOWERELD,
        self::KIDSCADEAU,
        self::KINDPAS,
        self::LEISURECARD,
        self::NATIONALEBIOSCOOPBON,
        self::NETSCARD,
        self::OBERTHUR,
        self::PATHEGIFTCARD,
        self::PAYEX,
        self::PODIUMCARD,
        self::RESURSGIFTCARD,
        self::ROTTERDAMPAS,
        self::GENERICGIFTCARD,
        self::SCHOOLSPULLENPAS,
        self::SPARNORD,
        self::SPAREBANK,
        self::SVS,
        self::UNIVERSALGIFTCARD,
        self::VVVCADEAUBON,
        self::VVVGIFTCARD,
        self::WEBSHOPGIFTCARD,
        self::WINKELCHEQUE,
        self::WINTERKLEDINGPAS,
        self::XPONCARD,
        self::YOURGIFT,
        self::PROSODIE_ILLICADO,
        //</editor-fold>
        self::TWINT
    ];

    private const SCHEME = 'scheme';
    private const AMERICAN_EXPRESS = 'amex';
    private const BANCONTACT_CARD = 'bcmc';
    private const CARNET = 'carnet';
    private const CARTES_BANCAIRES = 'cartebancaire';
    private const CHINA_UNIONPAY = 'cup';
    private const DINERS = 'diners';
    private const DISCOVER = 'discover';
    private const ELECTRON = 'electron';
    private const ELO = 'elo';
    private const HIPERCARD = 'hipercard';
    private const JCB = 'jcb';
    private const MAESTRO = 'maestro';
    private const MASTERCARD = 'mc';
    private const TROY = 'troy';
    private const VISA = 'visa';
    private const ONEY = 'oney';
    private const ONEY_3X = 'facilypay_3x';
    private const ONEY_4X = 'facilypay_4x';
    private const ONEY_6X = 'facilypay_6x';
    private const ONEY_10X = 'facilypay_10x';
    private const ONEY_12X = 'facilypay_12x';
    private const AFTERPAYTOUCH = 'afterpay_default';
    private const CLEARPAY = 'clearpay';
    private const KLARNA = 'klarna';
    private const KLARNA_ACCOUNT = 'klarna_account';
    private const MULTIBANCO = 'multibanco';
    private const ACH = 'ach';
    private const SEPA = 'sepadirectdebit';
    private const DIRECTDEBIT_GB = 'directdebit_GB';
    private const BLIK = 'blik';
    private const EPS = 'eps';
    private const GIROPAY = 'giropay';
    private const IDEAL = 'ideal';
    private const KLARNA_PAYNOW = 'klarna_paynow';
    private const MBWAY = 'mbway';
    private const MOBILEPAY = 'mobilepay';
    private const EBANKING_FI = 'ebanking_FI';
    private const ONLINEBANKING_IN = 'billdesk_online';
    private const ONLINEBANKING_PL = 'onlineBanking_PL';
    private const MOLPAY_EBANKING_TH = 'molpay_ebanking_TH';
    private const DIRECT_EBANKING = 'directEbanking';
    private const TRUSTLY = 'trustly';
    private const APPLEPAY = 'applepay';
    private const AMAZONPAY = 'amazonpay';
    private const ALIPAY = 'alipay';
    private const BCMC_MOBILE = 'bcmc_mobile';
    private const GOOGLEPAY = 'googlepay';
    private const PAY_WITH_GOOGLE = 'paywithgoogle';
    private const GCASH = 'gcash';
    private const MOMO_WALLET = 'momo_wallet';
    private const PAYPAL = 'paypal';
    private const SWISH = 'swish';
    private const VIPPS = 'vipps';
    private const ZIP = 'zip';
    private const WECHATPAYQR = 'wechatpayQR';

    //<editor-fold desc="Gift cards" defaultstate="collapsed">
    private const GIFTCARD = 'giftcard';
    private const AURIGA = 'auriga';
    private const BABYGIFTCARD = 'babygiftcard';
    private const BLOEMENGIFTCARD = 'bloemengiftcard';
    private const CASHCOMGIFTCARD = 'cashcomgiftcard';
    private const EAGLEEYE_VOUCHER = 'eagleeye_voucher';
    private const ENTERCARD = 'entercard';
    private const EXPERTGIFTCARD = 'expertgiftcard';
    private const FASHIONCHEQUE = 'fashioncheque';
    private const FIJNCADEAU = 'fijncadeau';
    private const VALUELINK = 'valuelink';
    private const FLEUROPBLOEMENBON = 'fleuropbloemenbon';
    private const FONQGIFTCARD = 'fonqgiftcard';
    private const GALLGALL = 'gallgall';
    private const GIVEX = 'givex';
    private const HALLMARKCARD = 'hallmarkcard';
    private const IGIVE = 'igive';
    private const IKANO = 'ikano';
    private const KADOWERELD = 'kadowereld';
    private const KIDSCADEAU = 'kidscadeau';
    private const KINDPAS = 'kindpas';
    private const LEISURECARD = 'leisurecard';
    private const NATIONALEBIOSCOOPBON = 'nationalebioscoopbon';
    private const NETSCARD = 'netscard';
    private const OBERTHUR = 'oberthur';
    private const PATHEGIFTCARD = 'pathegiftcard';
    private const PAYEX = 'payex';
    private const PODIUMCARD = 'podiumcard';
    private const RESURSGIFTCARD = 'resursgiftcard';
    private const ROTTERDAMPAS = 'rotterdampas';
    private const GENERICGIFTCARD = 'genericgiftcard';
    private const SCHOOLSPULLENPAS = 'schoolspullenpas';
    private const SPARNORD = 'sparnord';
    private const SPAREBANK = 'sparebank';
    private const SVS = 'svs';
    private const UNIVERSALGIFTCARD = 'universalgiftcard';
    private const VVVCADEAUBON = 'vvvcadeaubon';
    private const VVVGIFTCARD = 'vvvgiftcard';
    private const WEBSHOPGIFTCARD = 'webshopgiftcard';
    private const WINKELCHEQUE = 'winkelcheque';
    private const WINTERKLEDINGPAS = 'winterkledingpas';
    private const XPONCARD = 'xponcard';
    private const YOURGIFT = 'yourgift';
    private const PROSODIE_ILLICADO = 'prosodie_illicado';
    //</editor-fold>

    private const PAYSAFECARD = 'paysafecard';
    private const TWINT = 'twint';
    const GIFTCARD_BRANDS = [
        self::GIFTCARD,
        self::AURIGA,
        self::BABYGIFTCARD,
        self::BLOEMENGIFTCARD,
        self::CASHCOMGIFTCARD,
        self::EAGLEEYE_VOUCHER,
        self::ENTERCARD,
        self::EXPERTGIFTCARD,
        self::FASHIONCHEQUE,
        self::FIJNCADEAU,
        self::VALUELINK,
        self::FLEUROPBLOEMENBON,
        self::FONQGIFTCARD,
        self::GALLGALL,
        self::GIVEX,
        self::HALLMARKCARD,
        self::IGIVE,
        self::IKANO,
        self::KADOWERELD,
        self::KIDSCADEAU,
        self::KINDPAS,
        self::LEISURECARD,
        self::NATIONALEBIOSCOOPBON,
        self::NETSCARD,
        self::OBERTHUR,
        self::PATHEGIFTCARD,
        self::PAYEX,
        self::PODIUMCARD,
        self::RESURSGIFTCARD,
        self::ROTTERDAMPAS,
        self::GENERICGIFTCARD,
        self::SCHOOLSPULLENPAS,
        self::SPARNORD,
        self::SPAREBANK,
        self::SVS,
        self::UNIVERSALGIFTCARD,
        self::VVVCADEAUBON,
        self::VVVGIFTCARD,
        self::WEBSHOPGIFTCARD,
        self::WINKELCHEQUE,
        self::WINTERKLEDINGPAS,
        self::XPONCARD,
        self::YOURGIFT,
        self::PROSODIE_ILLICADO
    ];

    /**
     * @var string
     */
    private $type;

    /**
     * PaymentMethodType constructor.
     *
     * @param string $type
     */
    private function __construct(string $type)
    {
        $this->type = $type;
    }

    /**
     * String representation of the payment method type
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->type;
    }

    public function equals(string $code): bool
    {
        return $this->type === $code;
    }

    /**
     * Creates payment method code instance out of its string representation
     *
     * @param string $code
     *
     * @return PaymentMethodCode
     *
     * @throws InvalidPaymentMethodCodeException
     */
    public static function parse(string $code): PaymentMethodCode
    {
        if (!in_array($code, self::SUPPORTED_PAYMENT_METHODS)) {
            throw new InvalidPaymentMethodCodeException(new TranslatableLabel(
                sprintf('Payment method code is invalid %s.', $code),
                'checkout.invalidMethodCode',
                [$code]
            ));
        }

        return new self($code);
    }

    public static function getExtendedCodesList(array $codesToCheck): array
    {
        if (in_array(self::PAY_WITH_GOOGLE, $codesToCheck, true)) {
            $codesToCheck[] = self::GOOGLEPAY;
        }

        if (in_array(self::GOOGLEPAY, $codesToCheck, true)) {
            $codesToCheck[] = self::PAY_WITH_GOOGLE;
        }

        if (in_array(self::ONEY, $codesToCheck, true)) {
            $codesToCheck[] = self::ONEY_12X;
            $codesToCheck[] = self::ONEY_10X;
            $codesToCheck[] = self::ONEY_6X;
            $codesToCheck[] = self::ONEY_4X;
            $codesToCheck[] = self::ONEY_3X;
        }

        if (!empty(array_intersect($codesToCheck, self::GIFTCARD_BRANDS))) {
            $codesToCheck[] = self::GIFTCARD;
        }

        return array_unique($codesToCheck);
    }

    /**
     * @param string $code
     *
     * @return bool
     */
    public static function isOneyMethod(string $code): bool
    {
        return in_array($code,
            [self::ONEY_3X, self::ONEY_4X, self::ONEY_6X, self::ONEY_10X, self::ONEY_12X, self::ONEY]);
    }

    /**
     * @param string $code
     * @return bool
     */
    public static function isGiftCard(string $code): bool
    {
        return in_array($code, self::GIFTCARD_BRANDS);
    }

    public static function scheme(): PaymentMethodCode
    {
        return new self(self::SCHEME);
    }

    public static function facilyPay3x(): PaymentMethodCode
    {
        return new self(self::ONEY_3X);
    }

    public static function facilyPay4x(): PaymentMethodCode
    {
        return new self(self::ONEY_4X);
    }

    public static function facilyPay6x(): PaymentMethodCode
    {
        return new self(self::ONEY_6X);
    }

    public static function facilyPay10x(): PaymentMethodCode
    {
        return new self(self::ONEY_10X);
    }

    public static function facilyPay12x(): PaymentMethodCode
    {
        return new self(self::ONEY_12X);
    }

    public static function oney(): PaymentMethodCode
    {
        return new self(self::ONEY);
    }

    public static function afterPayTouch(): PaymentMethodCode
    {
        return new self(self::AFTERPAYTOUCH);
    }

    public static function clearPay(): PaymentMethodCode
    {
        return new self(self::CLEARPAY);
    }

    public static function klarna(): PaymentMethodCode
    {
        return new self(self::KLARNA);
    }

    public static function klarnaAccount(): PaymentMethodCode
    {
        return new self(self::KLARNA_ACCOUNT);
    }

    public static function multibanco(): PaymentMethodCode
    {
        return new self(self::MULTIBANCO);
    }

    public static function ach(): PaymentMethodCode
    {
        return new self(self::ACH);
    }

    public static function sepa(): PaymentMethodCode
    {
        return new self(self::SEPA);
    }

    public static function directDebitGb(): PaymentMethodCode
    {
        return new self(self::DIRECTDEBIT_GB);
    }

    public static function blik(): PaymentMethodCode
    {
        return new self(self::BLIK);
    }

    public static function eps(): PaymentMethodCode
    {
        return new self(self::EPS);
    }

    public static function giroPay(): PaymentMethodCode
    {
        return new self(self::GIROPAY);
    }

    public static function ideal(): PaymentMethodCode
    {
        return new self(self::IDEAL);
    }

    public static function klarnaPayNow(): PaymentMethodCode
    {
        return new self(self::KLARNA_PAYNOW);
    }

    public static function mbWay(): PaymentMethodCode
    {
        return new self(self::MBWAY);
    }

    public static function mobilePay(): PaymentMethodCode
    {
        return new self(self::MOBILEPAY);
    }

    public static function eBankingFi(): PaymentMethodCode
    {
        return new self(self::EBANKING_FI);
    }

    public static function onlineBankingIn(): PaymentMethodCode
    {
        return new self(self::ONLINEBANKING_IN);
    }

    public static function onlineBankingPl(): PaymentMethodCode
    {
        return new self(self::ONLINEBANKING_PL);
    }

    public static function molPayEBankingTh(): PaymentMethodCode
    {
        return new self(self::MOLPAY_EBANKING_TH);
    }

    public static function directEBanking(): PaymentMethodCode
    {
        return new self(self::DIRECT_EBANKING);
    }

    public static function trustly(): PaymentMethodCode
    {
        return new self(self::TRUSTLY);
    }

    public static function applePay(): PaymentMethodCode
    {
        return new self(self::APPLEPAY);
    }

    public static function amazonPay(): PaymentMethodCode
    {
        return new self(self::AMAZONPAY);
    }

    public static function aliPay(): PaymentMethodCode
    {
        return new self(self::ALIPAY);
    }

    public static function bcmcMobile(): PaymentMethodCode
    {
        return new self(self::BCMC_MOBILE);
    }

    public static function googlePay(): PaymentMethodCode
    {
        return new self(self::GOOGLEPAY);
    }

    public static function payWithGoogle(): PaymentMethodCode
    {
        return new self(self::PAY_WITH_GOOGLE);
    }

    public static function gCash(): PaymentMethodCode
    {
        return new self(self::GCASH);
    }

    public static function momoWallet(): PaymentMethodCode
    {
        return new self(self::MOMO_WALLET);
    }

    public static function payPal(): PaymentMethodCode
    {
        return new self(self::PAYPAL);
    }

    public static function swish(): PaymentMethodCode
    {
        return new self(self::SWISH);
    }

    public static function vipps(): PaymentMethodCode
    {
        return new self(self::VIPPS);
    }

    public static function zip(): PaymentMethodCode
    {
        return new self(self::ZIP);
    }

    public static function weChatPay(): PaymentMethodCode
    {
        return new self(self::WECHATPAYQR);
    }

    public static function giftCard(): PaymentMethodCode
    {
        return new self(self::GIFTCARD);
    }

    public static function paySafeCard(): PaymentMethodCode
    {
        return new self(self::PAYSAFECARD);
    }

    public static function twint(): PaymentMethodCode
    {
        return new self(self::TWINT);
    }

    public function isCaptureSupported(): bool
    {
        return in_array($this->type, self::CAPTURE_SUPPORTED, true);
    }

    public function isPartialCaptureSupported(): bool
    {
        return in_array($this->type, self::PARTIAL_CAPTURE_SUPPORTED, true);
    }

    public function isRefundSupported(): bool
    {
        return in_array($this->type, self::REFUND_SUPPORTED, true);
    }

    public function isPartialRefundSupported(): bool
    {
        return in_array($this->type, self::PARTIAL_REFUND_SUPPORTED, true);
    }
}
