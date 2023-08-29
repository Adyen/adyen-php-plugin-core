<?php

namespace Adyen\Core\BusinessLogic\Domain\Webhook\Exceptions;

use Adyen\Core\BusinessLogic\Domain\Translations\Model\BaseTranslatableException;
use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Throwable;

/**
 * Class MerchantDoesNotExistException
 *
 * @package Adyen\Core\BusinessLogic\Domain\Webhook\Exceptions
 */
class MerchantDoesNotExistException extends BaseTranslatableException
{
    public function __construct(TranslatableLabel $translatableLabel, Throwable $previous = null)
    {
        $this->code = 401;

        parent::__construct($translatableLabel, $previous);
    }
}
