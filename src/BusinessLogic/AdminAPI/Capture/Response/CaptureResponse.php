<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\Capture\Response;

use Adyen\Core\BusinessLogic\AdminAPI\Response\Response;

/**
 * Class CaptureResponse
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\Capture\Response
 */
class CaptureResponse extends Response
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
