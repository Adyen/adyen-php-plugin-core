<?php

namespace Adyen\Core\BusinessLogic\Domain\GeneralSettings\Models;

use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Exceptions\InvalidCaptureTypeException;
use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class CaptureType
 *
 * @package Adyen\Core\BusinessLogic\Domain\GeneralSettings\Models
 */
class CaptureType
{
    /**
     * Immediate string constant.
     */
    public const IMMEDIATE = 'immediate';

    /**
     * Delayed string constant.
     */
    public const DELAYED = 'delayed';

    /**
     * Manual string constant.
     */
    public const MANUAL = 'manual';

    /**
     * Unknown string constant.
     */
    public const UNKNOWN = 'unknown';

    /**
     * @var string
     */
    private $type;

    /**
     * @param string $type
     */
    private function __construct(string $type)
    {
        $this->type = $type;
    }

    /**
     * Called for immediate capture type.
     *
     * @return CaptureType
     */
    public static function immediate(): self
    {
        return new self(self::IMMEDIATE);
    }

    /**
     * Called for delayed capture type.
     *
     * @return CaptureType
     */
    public static function delayed(): self
    {
        return new self(self::DELAYED);
    }

    /**
     * Called for manual capture type.
     *
     * @return CaptureType
     */
    public static function manual(): self
    {
        return new self(self::MANUAL);
    }

    /**
     * Called for unknown capture type.
     *
     * @return CaptureType
     */
    public static function unknown(): self
    {
        return new self(self::UNKNOWN);
    }

    /**
     * Returns instance of CaptureType based on state string.
     *
     * @param string $state
     *
     * @return CaptureType
     *
     * @throws InvalidCaptureTypeException
     */
    public static function fromState(string $state): self
    {
        if ($state === self::MANUAL) {
            return self::manual();
        }

        if ($state === self::DELAYED) {
            return self::delayed();
        }

        if ($state === self::IMMEDIATE) {
            return self::immediate();
        }

        if ($state === self::UNKNOWN) {
            return self::unknown();
        }

        throw new InvalidCaptureTypeException(
            new TranslatableLabel(
                'Invalid capture type. Capture type must be immediate, manual or delayed',
                'generalSettings.captureTypeError'
            )
        );
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    public function equal(CaptureType $captureType): bool
    {
        return $this->getType() === $captureType->getType();
    }
}
