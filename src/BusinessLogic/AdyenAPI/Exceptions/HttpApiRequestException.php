<?php

namespace Adyen\Core\BusinessLogic\AdyenAPI\Exceptions;

use Adyen\Core\Infrastructure\Http\Exceptions\HttpRequestException;
use Adyen\Core\Infrastructure\Http\HttpResponse;
use Throwable;

/**
 * Class HttpApiRequestException
 *
 * @package Adyen\Core\BusinessLogic\AdyenAPI\Exceptions
 */
class HttpApiRequestException extends HttpRequestException
{
    /**
     * @var string[]
     */
    private $additionalData = [];
    /**
     * @var string
     */
    private $errorCode = '';
    /**
     * @var string
     */
    private $errorType = '';
    /**
     * @var string
     */
    private $pspReference = '';

    private function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function fromErrorResponse(HttpResponse $response): HttpApiRequestException
    {
        $responseBody = json_decode($response->getBody(), true);

        if (!$responseBody) {
            return new self(
                $response->getBody(),
                $response->getStatus()
            );
        }

        $instance = new self(
            array_key_exists('message', $responseBody) ? $responseBody['message'] : $response->getBody(),
            $response->getStatus()
        );

        if (array_key_exists('additionalData', $responseBody)) {
            $instance->additionalData = $responseBody['additionalData'];
        }

        if (array_key_exists('errorCode', $responseBody)) {
            $instance->errorCode = $responseBody['errorCode'];
        }

        if (array_key_exists('errorType', $responseBody)) {
            $instance->errorType = $responseBody['errorType'];
        }

        if (array_key_exists('pspReference', $responseBody)) {
            $instance->pspReference = $responseBody['pspReference'];
        }

        return $instance;
    }

    /**
     * @return string[]
     */
    public function getAdditionalData(): array
    {
        return $this->additionalData;
    }

    /**
     * @return string
     */
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    /**
     * @return string
     */
    public function getErrorType(): string
    {
        return $this->errorType;
    }

    /**
     * @return string
     */
    public function getPspReference(): string
    {
        return $this->pspReference;
    }
}
