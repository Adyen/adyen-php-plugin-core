<?php

namespace Adyen\Core\Tests\BusinessLogic\CheckoutAPI\CheckoutConfig\MockComponents;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\AvailablePaymentMethodsResponse;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PaymentMethodsRequest;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PaymentRequest;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\ResultCode;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\StartTransactionResult;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\UpdatePaymentDetailsRequest;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\UpdatePaymentDetailsResult;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Proxies\PaymentsProxy;

/**
 * Class MockPaymentsProxy
 *
 * @package Adyen\Core\Tests\BusinessLogic\CheckoutAPI\CheckoutConfig\MockComponents
 */
class MockPaymentsProxy implements PaymentsProxy
{

    /**
     * @var AvailablePaymentMethodsResponse
     */
    private $result;
    /**
     * @var true
     */
    private $isCalled = false;
    /**
     * @var PaymentRequest
     */
    private $lastRequest;

    public function startPaymentTransaction(PaymentRequest $request): StartTransactionResult
    {
        return new StartTransactionResult(ResultCode::authorised());
    }

    public function updatePaymentDetails(UpdatePaymentDetailsRequest $request): UpdatePaymentDetailsResult
    {
        return new UpdatePaymentDetailsResult(ResultCode::authorised());
    }

    public function getAvailablePaymentMethods(PaymentMethodsRequest $request): AvailablePaymentMethodsResponse
    {
        $this->isCalled = true;
        $this->lastRequest = $request;

        return $this->result ?? new AvailablePaymentMethodsResponse();
    }

    /**
     * @param AvailablePaymentMethodsResponse $result
     * @return void
     */
    public function setMockResult(AvailablePaymentMethodsResponse $result): void
    {
        $this->result = $result;
    }

    /**
     * @return true
     */
    public function getIsCalled(): bool
    {
        return $this->isCalled;
    }

    public function getLastRequest(): PaymentRequest
    {
        return $this->lastRequest;
    }
}
