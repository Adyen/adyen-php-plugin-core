<?php

namespace Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models\Events;

/**
 * Class Severity
 *
 * @package Adyen\Core\BusinessLogic\Domain\ShopNotifications\Models
 */
class Severity
{
    /**
     * Error string constant.
     */
    public const ERROR = 'error';

    /**
     * Warning string constant.
     */
    public const WARNING = 'warning';

    /**
     * Info string constant.
     */
    public const INFO = 'info';

    /**
     * @var string
     */
    private $severity;

    /**
     * @param string $severity
     */
    private function __construct(string $severity)
    {
        $this->severity = $severity;
    }

    /**
     * Called for error severity.
     *
     * @return Severity
     */
    public static function error(): self
    {
        return new self(self::ERROR);
    }

    /**
     * Called for warning severity.
     *
     * @return Severity
     */
    public static function warning(): self
    {
        return new self(self::WARNING);
    }

    /**
     * Called for info severity.
     *
     * @return Severity
     */
    public static function info(): self
    {
        return new self(self::INFO);
    }

    /**
     * @return string
     */
    public function getSeverity(): string
    {
        return $this->severity;
    }

    public function isSignificant(): bool
    {
        return in_array($this->severity, [self::ERROR, self::WARNING]);
    }
}
