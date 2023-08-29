<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\InvalidPaymentRequestResultCode;
use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class ResultCode
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models
 */
class ResultCode
{
    private const AUTHORISED = 'Authorised';
    private const CANCELLED = 'Cancelled';
    private const CHALLENGE_SHOPPER = 'ChallengeShopper';
    private const ERROR = 'Error';
    private const IDENTIFY_SHOPPER = 'IdentifyShopper';
    private const PENDING = 'Pending';
    private const PRESENT_TO_SHOPPER = 'PresentToShopper';
    private const RECEIVED = 'Received';
    private const REDIRECT_SHOPPER = 'RedirectShopper';
    private const REFUSED = 'Refused';
    private const VALID_RESULT_CODES = [
        self::AUTHORISED,
        self::CANCELLED,
        self::CHALLENGE_SHOPPER,
        self::ERROR,
        self::IDENTIFY_SHOPPER,
        self::PENDING,
        self::PRESENT_TO_SHOPPER,
        self::RECEIVED,
        self::REDIRECT_SHOPPER,
        self::REFUSED,
    ];
    private const SUCCESSFUL_RESULT_CODES = [
        self::AUTHORISED,
        self::CHALLENGE_SHOPPER,
        self::IDENTIFY_SHOPPER,
        self::PENDING,
        self::PRESENT_TO_SHOPPER,
        self::RECEIVED,
        self::REDIRECT_SHOPPER,
    ];
    /**
     * @var string
     */
    private $resultCode;

    /**
     * ResultCode constructor.
     *
     * @param string $resultCode
     * @throws InvalidPaymentRequestResultCode
     */
    private function __construct(string $resultCode)
    {
        if (!in_array($resultCode, self::VALID_RESULT_CODES)) {
            throw new InvalidPaymentRequestResultCode(
                new TranslatableLabel(
                    "Invalid payment result code ($resultCode).",
                    'checkout.invalidResult'
                )
            );
        }

        $this->resultCode = $resultCode;
    }

    public static function parse(string $resultCode): ResultCode
    {
        return new self($resultCode);
    }

    public static function authorised(): ResultCode
    {
        return self::parse(self::AUTHORISED);
    }

    public static function cancelled(): ResultCode
    {
        return self::parse(self::CANCELLED);
    }

    public static function challengeShopper(): ResultCode
    {
        return self::parse(self::CHALLENGE_SHOPPER);
    }

    public static function error(): ResultCode
    {
        return self::parse(self::ERROR);
    }

    public static function identifyShopper(): ResultCode
    {
        return self::parse(self::IDENTIFY_SHOPPER);
    }

    public static function pending(): ResultCode
    {
        return self::parse(self::PENDING);
    }

    public static function presentToShopper(): ResultCode
    {
        return self::parse(self::PRESENT_TO_SHOPPER);
    }

    public static function received(): ResultCode
    {
        return self::parse(self::RECEIVED);
    }

    public static function redirectShopper(): ResultCode
    {
        return self::parse(self::REDIRECT_SHOPPER);
    }

    public static function refused(): ResultCode
    {
        return self::parse(self::REFUSED);
    }

    public function shouldPresentToShopper(): bool
    {
        return $this->resultCode === self::PRESENT_TO_SHOPPER;
    }

    public function isRecieved(): bool
    {
        return $this->resultCode === self::RECEIVED;
    }

    /**
     * @return string
     */
    public function getResultCode(): string
    {
        return $this->resultCode;
    }

    /**
     * Returns the success indicator of a result code instance.
     *
     * @return bool True if the result code is considered successful; false otherwise.
     */
    public function isSuccessful(): bool
    {
        return in_array($this->resultCode, self::SUCCESSFUL_RESULT_CODES, true);
    }

    public function __toString()
    {
        return $this->getResultCode();
    }
}
