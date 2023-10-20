<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\Processors\PaymentLinkRequest;

use Adyen\Core\Infrastructure\ServiceRegister;

/**
 * Class Registry
 *
 * @template T of PaymentLinkRequestProcessor
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors
 */
class PaymentLinkRequestProcessorsRegistry
{
    /**
     * Map of global registered processors that will be applied for all payment types
     *
     * @var array<class-string<T>, class-string<T>>
     */
    private static $globalProcessors = [];

    /**
     * Registers global payment request processor that can be applied for all payment method types
     *
     * @param class-string<T> $processorClass
     * @return void
     */
    public static function registerGlobal(string $processorClass): void
    {
        static::$globalProcessors[$processorClass] = $processorClass;
    }

    /**
     * Get all Payment link processors.
     *
     * @return PaymentLinkRequestProcessor[]
     */
    public static function getProcessors(): array
    {
        return array_map(static function ($processor): PaymentLinkRequestProcessor {
            return ServiceRegister::getService($processor);
        }, static::$globalProcessors);
    }
}
