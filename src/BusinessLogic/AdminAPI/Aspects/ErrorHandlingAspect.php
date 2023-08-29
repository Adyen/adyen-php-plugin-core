<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\Aspects;

use Adyen\Core\BusinessLogic\AdminAPI\Response\TranslatableErrorResponse;
use Adyen\Core\BusinessLogic\Bootstrap\Aspect\Aspect;
use Adyen\Core\BusinessLogic\Domain\Translations\Model\BaseTranslatableException;
use Adyen\Core\BusinessLogic\Domain\Translations\Model\BaseTranslatableUnhandledException;
use Adyen\Core\Infrastructure\Logger\Logger;
use Exception;
use Throwable;

/**
 * Class ErrorHandlingAspect
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\Aspects
 */
class ErrorHandlingAspect implements Aspect
{
    /**
     * @throws Exception
     */
    public function applyOn(callable $callee, array $params = [])
    {
        try {
            $response = call_user_func_array($callee, $params);
        } catch (BaseTranslatableException $e) {
            Logger::logError(
                $e->getMessage(),
                'Core',
                [
                    'message' => $e->getMessage(),
                    'type' => get_class($e),
                    'trace' => $e->getTraceAsString(),
                ]
            );

            $response = TranslatableErrorResponse::fromError($e);
        } catch (Throwable $e) {
            Logger::logError(
                'Unhandled error occurred.',
                'Core',
                [
                    'message' => $e->getMessage(),
                    'type' => get_class($e),
                    'trace' => $e->getTraceAsString(),
                ]
            );

            $exception = new BaseTranslatableUnhandledException($e);
            $response = TranslatableErrorResponse::fromError($exception);
        }

        return $response;
    }
}
