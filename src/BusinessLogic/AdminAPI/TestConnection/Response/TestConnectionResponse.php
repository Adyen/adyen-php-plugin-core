<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\TestConnection\Response;

use Adyen\Core\BusinessLogic\AdminAPI\Response\Response;

/**
 * Class TestConnectionResponse
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\ValidateConnection\Response
 */
class TestConnectionResponse extends Response
{
    /**
     * @var bool
     */
    private $status;

    /**
     * @var string
     */
    private $message;

    /**
     * @param bool $status
     * @param string|null $message
     */
    public function __construct(bool $status, ?string $message)
    {
        $this->status = $status;
        $this->message = $message;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'message' => $this->message
        ];
    }
}
