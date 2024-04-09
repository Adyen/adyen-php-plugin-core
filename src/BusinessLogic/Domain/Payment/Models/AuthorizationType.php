<?php

namespace Adyen\Core\BusinessLogic\Domain\Payment\Models;

use Adyen\Core\BusinessLogic\Domain\Payment\Models\Exceptions\InvalidAuthorizationTypeException;
use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class AuthorizationType.
 *
 * @package Adyen\Core\BusinessLogic\Domain\Payment\Models
 */
class AuthorizationType
{
    /**
     * Pre-authorization string constant.
     */
    public const PRE_AUTHORIZATION = 'PreAuth';

    /**
     * Final authorization string constant.
     */
    public const FINAL_AUTHORIZATION = 'FinalAuth';

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
     * Called for pre-authorization type.
     *
     * @return AuthorizationType
     */
    public static function preAuthorization(): self
    {
        return new self(self::PRE_AUTHORIZATION);
    }

    /**
     * Called for final authorization type.
     *
     * @return AuthorizationType
     */
    public static function finalAuthorization(): self
    {
        return new self(self::FINAL_AUTHORIZATION);
    }

    /**
     * Returns instance of AuthorizationType based on state string.
     *
     * @param string $state
     *
     * @return AuthorizationType
     *
     * @throws InvalidAuthorizationTypeException
     */
    public static function fromState(string $state): self
    {
        if ($state === self::PRE_AUTHORIZATION) {
            return self::preAuthorization();
        }

        if ($state === self::FINAL_AUTHORIZATION) {
            return self::finalAuthorization();
        }

        throw new InvalidAuthorizationTypeException(
            new TranslatableLabel(
                'Invalid authorization type. Token type must be PreAuth or FinalAuth',
                'paymentMethod.authorizationTypeError'
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
     * @param AuthorizationType $tokenType
     *
     * @return bool
     */
    public function equal(AuthorizationType $tokenType): bool
    {
        return $this->getType() === $tokenType->getType();
    }
}
