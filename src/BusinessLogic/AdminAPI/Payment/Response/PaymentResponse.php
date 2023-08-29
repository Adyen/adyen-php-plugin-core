<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\Payment\Response;

use Adyen\Core\BusinessLogic\AdminAPI\Response\Response;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\PaymentMethod;

/**
 * Class PaymentResponse
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\Payment\Response
 */
class PaymentResponse extends Response
{
    /**
     * @var PaymentMethod[]
     */
    private $paymentMethods;

    /**
     * @param PaymentMethod[] $paymentMethods
     */
    public function __construct(array $paymentMethods)
    {
        $this->paymentMethods = $paymentMethods;
    }

    /**
     * Transforms payment methods to array.
     *
     * @return array
     */
    public function toArray(): array
    {
        $paymentMethodsArray = [];

        foreach ($this->paymentMethods as $paymentMethod) {
            $paymentMethodsArray[] = $this->transformPaymentMethod($paymentMethod);
        }

        return $paymentMethodsArray;
    }

    /**
     * @param PaymentMethod $paymentMethod
     *
     * @return array
     */
    private function transformPaymentMethod(PaymentMethod $paymentMethod): array
    {
        return [
            'methodId' => $paymentMethod->getMethodId(),
            'logo' => $paymentMethod->getLogo(),
            'name' => $paymentMethod->getName(),
            'status' => $paymentMethod->isStatus(),
            'currencies' => $paymentMethod->getCurrencies(),
            'countries' => $paymentMethod->getCountries(),
            'paymentType' => $paymentMethod->getPaymentType(),
            'code' => $paymentMethod->getCode()
        ];
    }
}
