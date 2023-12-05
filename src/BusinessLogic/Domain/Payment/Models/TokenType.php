<?php

namespace Adyen\Core\BusinessLogic\Domain\Payment\Models;

use Adyen\Core\BusinessLogic\Domain\Payment\Models\Exceptions\InvalidTokenTypeException;
use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class TokenType
 *
 * @package Adyen\Core\BusinessLogic\Domain\Payment\Models
 */
class TokenType
{
    /**
     * Card on file string constant.
     */
    public const CARD_ON_FILE = 'CardOnFile';

    /**
     * Subscription string constant.
     */
    public const SUBSCRIPTION = 'Subscription';

    /**
     * Unscheduled Card On File string constant.
     */
    public const UNSCHEDULED_CARD_ON_FILE = 'UnscheduledCardOnFile';

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
     * Called for card on file token type.
     *
     * @return TokenType
     */
    public static function cardOnFile(): self
    {
        return new self(self::CARD_ON_FILE);
    }

    /**
     * Called for subscription token type.
     *
     * @return TokenType
     */
    public static function subscription(): self
    {
        return new self(self::SUBSCRIPTION);
    }

    /**
     * Called for unscheduled card on file token type.
     *
     * @return TokenType
     */
    public static function unscheduledCardOnFile(): self
    {
        return new self(self::UNSCHEDULED_CARD_ON_FILE);
    }

    /**
     * Returns instance of TokenType based on state string.
     *
     * @param string $state
     *
     * @return TokenType
     *
     * @throws InvalidTokenTypeException
     */
    public static function fromState(string $state): self
    {
        if ($state === self::CARD_ON_FILE) {
            return self::cardOnFile();
        }

        if ($state === self::SUBSCRIPTION) {
            return self::subscription();
        }

        if ($state === self::UNSCHEDULED_CARD_ON_FILE) {
            return self::unscheduledCardOnFile();
        }

        throw new InvalidTokenTypeException(
            new TranslatableLabel(
                'Invalid token type. Token type must be CardOnFile, Subscription or UnscheduledCardOnFile',
                'paymentMethod.tokenTypeError'
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

    /**
     * @param TokenType $tokenType
     *
     * @return bool
     */
    public function equal(TokenType $tokenType): bool
    {
        return $this->getType() === $tokenType->getType();
    }
}
