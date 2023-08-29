<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\Response;

use Adyen\Core\BusinessLogic\Domain\Translations\Model\BaseTranslatableException;

/**
 * Class TranslatableErrorResponse
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\Response
 */
class TranslatableErrorResponse extends ErrorResponse
{
    /**
     * @var BaseTranslatableException
     */
    protected $error;

    /**
     * @param BaseTranslatableException $error
     */
    public function __construct(BaseTranslatableException $error)
    {
        parent::__construct($error);
    }

    public function toArray(): array
    {
        return [
            'errorCode' => $this->error->getTranslatableLabel()->getCode(),
            'errorMessage' => $this->error->getTranslatableLabel()->getMessage(),
            'errorParameters' => $this->error->getTranslatableLabel()->getParams(),
        ];
    }
}
