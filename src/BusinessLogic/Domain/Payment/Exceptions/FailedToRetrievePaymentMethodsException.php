<?php

namespace Adyen\Core\BusinessLogic\Domain\Payment\Exceptions;

use Adyen\Core\BusinessLogic\Domain\Translations\Model\BaseTranslatableException;
use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Adyen\Core\Infrastructure\Http\HttpClient;
use Throwable;

/**
 * Class FailedToRetrievePaymentMethodsException
 *
 * @package Adyen\Core\BusinessLogic\Domain\Payment\Exceptions
 */
class FailedToRetrievePaymentMethodsException extends BaseTranslatableException
{
    /**
     * @param TranslatableLabel $translatableLabel
     * @param Throwable|null $previous
     */
    public function __construct(TranslatableLabel $translatableLabel, Throwable $previous = null)
    {
        if ($previous->getCode() === HttpClient::HTTP_STATUS_CODE_FORBIDDEN) {
            $this->code = 401;
        }

        parent::__construct($translatableLabel, $previous);
    }
}
