<?php

namespace Adyen\Core\BusinessLogic\Domain\Stores\Exceptions;

use Adyen\Core\BusinessLogic\Domain\Translations\Model\BaseTranslatableException;
use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Exception;

/**
 * Class FailedToRetrieveStoresException
 *
 * @package Adyen\Core\BusinessLogic\Domain\Stores\Exceptions
 */
class FailedToRetrieveStoresException extends BaseTranslatableException
{
    public function __construct(Exception $previous)
    {
        parent::__construct(
            new TranslatableLabel('Failed to retrieve stores.', 'general.failedToRetrieveStores'),
            $previous
        );
    }
}
