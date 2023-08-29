<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\Cancel\Response;

use Adyen\Core\BusinessLogic\AdminAPI\Response\Response;

/**
 * Class CancelResponse
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\Cancel\Response
 */
class CancelResponse extends Response
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
