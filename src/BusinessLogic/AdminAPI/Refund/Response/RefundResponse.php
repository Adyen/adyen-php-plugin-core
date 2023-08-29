<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\Refund\Response;

use Adyen\Core\BusinessLogic\AdminAPI\Response\Response;

/**
 * Class RefundResponse
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\Refund\Response
 */
class RefundResponse extends Response
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
        return [];
    }
}
