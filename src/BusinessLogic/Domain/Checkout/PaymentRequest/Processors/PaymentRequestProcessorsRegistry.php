<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PaymentMethodCode;
use Adyen\Core\Infrastructure\ServiceRegister;

/**
 * Class Registry
 *
 * @template T of PaymentRequestProcessor
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors
 */
class PaymentRequestProcessorsRegistry
{
    /**
     * Map of global registered processors that will be applied for all payment types
     *
     * @var array<class-string<T>, class-string<T>>
     */
    private static $globalProcessors = [];

    /**
     * Map of payment type specific registered processors
     *
     * @var array<string, array<class-string<T>, class-string<T>>
     */
    private static $typedProcessors = [];

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
     * Registers payment method specific processor that can be applied only for specified payment method type
     *
     * @param PaymentMethodCode $type
     * @param class-string<T> $processorClass
     * @return void
     */
    public static function registerByPaymentType(PaymentMethodCode $type, string $processorClass): void
    {
        static::$typedProcessors[(string)$type][$processorClass] = $processorClass;
    }

    /**
     * Gets all applicable payment request processors for a given payment method type
     *
     * @param PaymentMethodCode $type
     * @return PaymentRequestProcessor[] Applicable processors (includes both global and type-specific)
     */
    public static function getProcessors(PaymentMethodCode $type): array
    {
        return array_map(static function (string $paymentClass): PaymentRequestProcessor {
            return ServiceRegister::getService($paymentClass);
        }, array_merge(static::$globalProcessors, static::$typedProcessors[(string)$type] ?? []));
    }
}
