<?php

namespace Adyen\Core\BusinessLogic\Domain\Connection\Exceptions;

use Adyen\Core\BusinessLogic\Domain\Translations\Model\BaseTranslatableException;
use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Throwable;

/**
 * Class ModeChangedException
 *
 * @package Adyen\Core\BusinessLogic\Domain\Connection\Exceptions
 */
class ModeChangedException extends BaseTranslatableException
{
    public function __construct(TranslatableLabel $translatableLabel, Throwable $previous = null)
    {
        $this->code = 401;

        parent::__construct($translatableLabel, $previous);
    }
}
