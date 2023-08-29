<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\Payment\Controller;

use Adyen\Core\BusinessLogic\AdminAPI\Payment\Request\PaymentMethodRequest;
use Adyen\Core\BusinessLogic\AdminAPI\Payment\Response\AvailableMethodsResponse;
use Adyen\Core\BusinessLogic\AdminAPI\Payment\Response\DeleteResponse;
use Adyen\Core\BusinessLogic\AdminAPI\Payment\Response\PaymentMethodResponse;
use Adyen\Core\BusinessLogic\AdminAPI\Payment\Response\PaymentResponse;
use Adyen\Core\BusinessLogic\AdminAPI\Payment\Response\UpsertResponse;
use Adyen\Core\BusinessLogic\Domain\Integration\Payment\ShopPaymentService;
use Adyen\Core\BusinessLogic\Domain\Payment\Exceptions\FailedToRetrievePaymentMethodsException;
use Adyen\Core\BusinessLogic\Domain\Payment\Exceptions\PaymentMethodDataEmptyException;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\PaymentMethod;
use Adyen\Core\BusinessLogic\Domain\Payment\Services\PaymentService;
use Exception;

/**
 * Class PaymentController
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\Payment\Controller
 */
class PaymentController
{
    /**
     * @var PaymentService
     */
    protected $paymentService;
    /**
     * @var ShopPaymentService
     */
    protected $shopPaymentService;

    /**
     * @param PaymentService $paymentService
     * @param ShopPaymentService $shopPaymentService
     */
    public function __construct(PaymentService $paymentService, ShopPaymentService $shopPaymentService)
    {
        $this->paymentService = $paymentService;
        $this->shopPaymentService = $shopPaymentService;
    }

    /**
     * Retrieves all configured payment methods.
     *
     * @return PaymentResponse
     *
     * @throws FailedToRetrievePaymentMethodsException
     */
    public function getConfiguredPaymentMethods(): PaymentResponse
    {
        return new PaymentResponse($this->paymentService->getConfiguredMethods());
    }

    /**
     * Retrieves payment method by id.
     *
     * @param string $id
     *
     * @return PaymentMethodResponse
     *
     * @throws Exception
     */
    public function getMethodById(string $id): PaymentMethodResponse
    {
        return new PaymentMethodResponse($this->paymentService->getPaymentMethodById($id));
    }

    /**
     * Retrieves available payment methods.
     *
     * @return AvailableMethodsResponse
     *
     * @throws FailedToRetrievePaymentMethodsException
     */
    public function getAvailablePaymentMethods(): AvailableMethodsResponse
    {
        return new AvailableMethodsResponse($this->paymentService->getAvailableMethods());
    }

    /**
     * Saves payment method configuration.
     *
     * @param PaymentMethodRequest $methodRequest
     *
     * @return UpsertResponse
     *
     * @throws PaymentMethodDataEmptyException
     * @throws Exception
     */
    public function saveMethodConfiguration(PaymentMethodRequest $methodRequest): UpsertResponse
    {
        /** @var PaymentMethod $method */
        $method = $methodRequest->transformToDomainModel();

        $this->paymentService->saveMethodConfiguration($method);
        $this->shopPaymentService->createPaymentMethod($method);

        return new UpsertResponse();
    }

    /**
     * Updates payment method configuration.
     *
     * @param PaymentMethodRequest $methodRequest
     *
     * @return UpsertResponse
     *
     * @throws PaymentMethodDataEmptyException
     * @throws Exception
     */
    public function updateMethodConfiguration(PaymentMethodRequest $methodRequest): UpsertResponse
    {
        /** @var PaymentMethod $method */
        $method = $methodRequest->transformToDomainModel();

        $this->paymentService->updateMethodConfiguration($method);
        $this->shopPaymentService->updatePaymentMethod($method);

        return new UpsertResponse();
    }

    /**
     * Deletes payment method configuration by id.
     *
     * @param string $id
     *
     * @return DeleteResponse
     *
     * @throws Exception
     */
    public function deletePaymentMethodById(string $id): DeleteResponse
    {
        $this->shopPaymentService->deletePaymentMethod($id);
        $this->paymentService->deletePaymentMethodById($id);

        return new DeleteResponse();
    }
}
