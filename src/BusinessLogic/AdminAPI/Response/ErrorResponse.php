<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\Response;

use Adyen\Core\BusinessLogic\AdminAPI\Aspects\ErrorHandlingAspect;

/**
 * Class ErrorResponse
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\Response
 */
class ErrorResponse extends Response
{
    /**
     * @inheritdoc
     */
    protected $successful = false;

	/**
	 * @var int
	 */
	protected $statusCode = 400;

    /**
     * @var \Throwable
     */
    protected $error;

    protected function __construct(\Throwable $error)
    {
        $this->error = $error;
		$this->statusCode = $error->getCode() > 0 ? $error->getCode() : 400;
    }

    /**
     * Implementation is swallowing all undefined calls to avoid undefined method call exceptions when
     * @see ErrorHandlingAspect already hanled the API call exception but because of chaining calle will trigger
     * API controller messages on instance of the @see self.
     *
     * @param $methodName
     * @param $arguments
     *
     * @return self Already handled error response
     */
    public function __call($methodName, $arguments)
    {
        return $this;
    }

    public static function fromError(\Throwable $e): self
    {
        return new static($e);
    }

    public function toArray(): array
    {
        return [
            'errorCode' => $this->statusCode,
            'errorMessage' => $this->error->getMessage(),
        ];
    }
}
