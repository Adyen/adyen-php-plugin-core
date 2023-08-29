<?php

namespace Adyen\Core\BusinessLogic\Domain\Connection\Exceptions;

use Adyen\Core\BusinessLogic\Domain\Translations\Model\BaseTranslatableException;
use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Throwable;

/**
 * Class InvalidApiKeyException
 *
 * @package Adyen\Core\BusinessLogic\Domain\Connection\Exceptions
 */
class InvalidApiKeyException extends BaseTranslatableException
{
    public function __construct(TranslatableLabel $translatableLabel, Throwable $previous = null)
    {
        $this->code = 401;

        parent::__construct($translatableLabel, $previous);
    }
}
