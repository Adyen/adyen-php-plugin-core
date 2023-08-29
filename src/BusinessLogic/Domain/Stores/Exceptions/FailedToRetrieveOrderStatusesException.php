<?php

namespace Adyen\Core\BusinessLogic\Domain\Stores\Exceptions;

use Adyen\Core\BusinessLogic\Domain\Translations\Model\BaseTranslatableException;
use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Exception;

/**
 * Class FailedToRetrieveOrderStatusesException
 *
 * @package Adyen\Core\BusinessLogic\Domain\Stores\Exceptions
 */
class FailedToRetrieveOrderStatusesException extends BaseTranslatableException
{
    public function __construct(Exception $previous)
    {
        parent::__construct(
            new TranslatableLabel(
                'Failed to retrieve order statuses.',
                'general.failedToRetrieveOrderStatuses'
            ),
            $previous
        );
    }
}
