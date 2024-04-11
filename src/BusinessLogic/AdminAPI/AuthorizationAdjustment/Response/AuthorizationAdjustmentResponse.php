<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\AuthorizationAdjustment\Response;

use Adyen\Core\BusinessLogic\AdminAPI\Response\Response;

/**
 * Class AuthorizationAdjustmentResponse.
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\AuthorizationAdjustment\Response
 */
class AuthorizationAdjustmentResponse extends Response
{
    /**
     * @var bool
     */
    private $isSuccessful;

    /**
     * @param bool $isSuccessful
     */
    public function __construct(bool $isSuccessful)
    {
        $this->isSuccessful = $isSuccessful;
    }

    /**
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return $this->isSuccessful;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return ['success' => $this->isSuccessful];
    }
}
