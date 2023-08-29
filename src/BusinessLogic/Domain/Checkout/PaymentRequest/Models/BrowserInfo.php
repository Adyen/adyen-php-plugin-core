<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models;

/**
 * Class BrowserInfo
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models
 */
class BrowserInfo
{
    /**
     * @var string
     */
    private $acceptHeader;
    /**
     * @var int
     */
    private $colorDepth;
    /**
     * @var bool
     */
    private $javaEnabled;
    /**
     * @var string
     */
    private $language;
    /**
     * @var int
     */
    private $screenHeight;
    /**
     * @var int
     */
    private $screenWidth;
    /**
     * @var int
     */
    private $timeZoneOffset;
    /**
     * @var string
     */
    private $userAgent;

    /**
     * @param string $acceptHeader
     * @param string $userAgent
     * @param int $colorDepth
     * @param bool $javaEnabled
     * @param string $language
     * @param int $screenHeight
     * @param int $screenWidth
     * @param int $timeZoneOffset
     */
    public function __construct(
        string $acceptHeader,
        string $userAgent = '',
        int    $colorDepth = 24,
        bool   $javaEnabled = true,
        string $language = 'en-US',
        int    $screenHeight = 0,
        int    $screenWidth = 0,
        int    $timeZoneOffset = 0
    )
    {
        $this->acceptHeader = $acceptHeader;
        $this->colorDepth = $colorDepth;
        $this->javaEnabled = $javaEnabled;
        $this->language = $language;
        $this->screenHeight = $screenHeight;
        $this->screenWidth = $screenWidth;
        $this->timeZoneOffset = $timeZoneOffset;
        $this->userAgent = $userAgent;
    }

    /**
     * @return string
     */
    public function getAcceptHeader(): string
    {
        return $this->acceptHeader;
    }

    /**
     * @return int
     */
    public function getColorDepth(): int
    {
        return $this->colorDepth;
    }

    /**
     * @return bool
     */
    public function isJavaEnabled(): bool
    {
        return $this->javaEnabled;
    }

    /**
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->language;
    }

    /**
     * @return int
     */
    public function getScreenHeight(): int
    {
        return $this->screenHeight;
    }

    /**
     * @return int
     */
    public function getScreenWidth(): int
    {
        return $this->screenWidth;
    }

    /**
     * @return int
     */
    public function getTimeZoneOffset(): int
    {
        return $this->timeZoneOffset;
    }

    /**
     * @return string
     */
    public function getUserAgent(): string
    {
        return $this->userAgent;
    }
}
