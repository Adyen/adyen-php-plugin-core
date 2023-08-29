<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\Response;

/**
 * Class Response
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\Response
 */
abstract class Response
{
    /**
     * @var bool
     */
    protected $successful = true;

    /**
     * @var int
     */
    protected $statusCode = 200;

    /**
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return $this->successful;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return array
     */
    abstract public function toArray(): array;
}
