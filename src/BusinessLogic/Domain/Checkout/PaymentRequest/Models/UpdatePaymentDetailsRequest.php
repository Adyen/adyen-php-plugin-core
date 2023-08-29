<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models;

/**
 * Class UpdatePaymentDetailsRequest
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models
 */
class UpdatePaymentDetailsRequest
{
    /**
     * List of allowed payment details keys for API version v69
     */
    private const ALLOWED_PAYMENT_DETAIL_KEYS = [
        'MD' => null,
        'PaReq' => null,
        'PaRes' => null,
        'billingToken' => null,
        'cupsecureplus.smscode' => null,
        'facilitatorAccessToken' => null,
        'oneTimePasscode' => null,
        'orderID' => null,
        'payerID' => null,
        'payload' => null,
        'paymentID' => null,
        'paymentStatus' => null,
        'redirectResult' => null,
        'resultCode' => null,
        'threeDSResult' => null,
        'threeds2.challengeResult' => null,
        'threeds2.fingerprint' => null,
    ];

    /**
     * @var array
     */
    private $details;
    /**
     * @var string
     */
    private $paymentData;

    private function __construct(array $details, string $paymentData = '')
    {
        $this->details = $details;
        $this->paymentData = $paymentData;
    }

    /**
     * Creates the instance of the payment request details with only the valid request keys from $rawDetails, ignoring
     * all the invalid input keys.
     *
     * @param array $rawData
     * @return UpdatePaymentDetailsRequest
     */
    public static function parse(array $rawData): UpdatePaymentDetailsRequest
    {
        $details = [];
        $paymentData = '';
        if (array_key_exists('details', $rawData)) {
            $details = array_intersect_key($rawData['details'], self::ALLOWED_PAYMENT_DETAIL_KEYS);
        }

        if (array_key_exists('paymentData', $rawData)) {
            $paymentData = $rawData['paymentData'];
        }

        return new self($details, $paymentData);
    }

    /**
     * Gets the details for payment details request
     *
     * @return array
     */
    public function getDetails(): array
    {
        return $this->details;
    }

    /**
     * @return string
     */
    public function getPaymentData(): string
    {
        return $this->paymentData;
    }
}
