<?php

namespace Adyen\Core\BusinessLogic\CheckoutAPI;

use Adyen\Core\BusinessLogic\AdminAPI\Aspects\ErrorHandlingAspect;
use Adyen\Core\BusinessLogic\AdminAPI\Aspects\StoreContextAspect;
use Adyen\Core\BusinessLogic\Bootstrap\Aspect\Aspects;
use Adyen\Core\BusinessLogic\CheckoutAPI\CheckoutConfig\Controller\CheckoutConfigController;
use Adyen\Core\BusinessLogic\CheckoutAPI\Donations\Controller\DonationController;
use Adyen\Core\BusinessLogic\CheckoutAPI\PaymentRequest\Controller\PaymentRequestController;
use Exception;

/**
 * Class AdminAPI. Integrations should use this class for communicating with Admin API.
 *
 * @package Adyen\Core\BusinessLogic\CheckoutAPI
 */
class CheckoutAPI
{
    private function __construct()
    {
    }

    /**
     * @return CheckoutAPI
     */
    public static function get(): object
    {
        return Aspects::run(new ErrorHandlingAspect())->beforeEachMethodOfInstance(new CheckoutAPI());
    }

    /**
     * @param string $storeId
     *
     * @return CheckoutConfigController
     */
    public function checkoutConfig(string $storeId): object
    {
        return Aspects
            ::run(new ErrorHandlingAspect())
            ->andRun(new StoreContextAspect($storeId))
            ->beforeEachMethodOfService(CheckoutConfigController::class);
    }

    /**
     * @param string $storeId
     *
     * @return PaymentRequestController
     *
     * @throws Exception
     */
    public function paymentRequest(string $storeId): object
    {
        return Aspects
            ::run(new ErrorHandlingAspect())
            ->andRun(new StoreContextAspect($storeId))
            ->beforeEachMethodOfService(PaymentRequestController::class);
    }

    /**
     * @param string $storeId
     *
     * @return DonationController
     *
     * @throws Exception
     */
    public function donation(string $storeId): object
    {
        return Aspects
            ::run(new ErrorHandlingAspect())
            ->andRun(new StoreContextAspect($storeId))
            ->beforeEachMethodOfService(DonationController::class);
    }
}
