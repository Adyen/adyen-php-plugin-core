<?php

namespace Adyen\Core\BusinessLogic\AdminAPI;

use Adyen\Core\BusinessLogic\AdminAPI\AdyenGivingSettings\Controller\AdyenGivingSettingsController;
use Adyen\Core\BusinessLogic\AdminAPI\Aspects\ErrorHandlingAspect;
use Adyen\Core\BusinessLogic\AdminAPI\Aspects\StoreContextAspect;
use Adyen\Core\BusinessLogic\AdminAPI\Cancel\Controller\CancelController;
use Adyen\Core\BusinessLogic\AdminAPI\Capture\Controller\CaptureController;
use Adyen\Core\BusinessLogic\AdminAPI\Connection\Controller\ConnectionController;
use Adyen\Core\BusinessLogic\AdminAPI\Disconnect\Controller\DisconnectController;
use Adyen\Core\BusinessLogic\AdminAPI\GeneralSettings\Controller\GeneralSettingsController;
use Adyen\Core\BusinessLogic\AdminAPI\InfoSettings\Controller\AutoTestController;
use Adyen\Core\BusinessLogic\AdminAPI\InfoSettings\Controller\DebugController;
use Adyen\Core\BusinessLogic\AdminAPI\InfoSettings\Controller\SystemInfoController;
use Adyen\Core\BusinessLogic\AdminAPI\InfoSettings\Controller\WebhookValidationController;
use Adyen\Core\BusinessLogic\AdminAPI\Integration\Controller\IntegrationController;
use Adyen\Core\BusinessLogic\AdminAPI\Refund\Controller\RefundController;
use Adyen\Core\BusinessLogic\AdminAPI\ShopNotifications\Controller\ShopNotificationsController;
use Adyen\Core\BusinessLogic\AdminAPI\Versions\Controller\VersionInfoController;
use Adyen\Core\BusinessLogic\AdminAPI\WebhookNotifications\Controller\WebhookNotificationController;
use Adyen\Core\BusinessLogic\AdminAPI\OrderMappings\Controller\OrderMappingsController;
use Adyen\Core\BusinessLogic\AdminAPI\Payment\Controller\PaymentController;
use Adyen\Core\BusinessLogic\AdminAPI\Stores\Controller\StoreController;
use Adyen\Core\BusinessLogic\AdminAPI\TestConnection\Controller\TestConnectionController;
use Adyen\Core\BusinessLogic\Bootstrap\Aspect\Aspects;
use Adyen\Core\BusinessLogic\AdminAPI\Merchant\Controller\MerchantController;
use Exception;

/**
 * Class AdminAPI. Integrations should use this class for communicating with Admin API.
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI
 */
class AdminAPI
{
    private function __construct()
    {
    }

    /**
     * @return AdminAPI
     */
    public static function get(): object
    {
        return Aspects::run(new ErrorHandlingAspect())->beforeEachMethodOfInstance(new AdminAPI());
    }

    /**
     * @return IntegrationController
     */
    public function integration(string $storeId): object
    {
        return Aspects
            ::run(new ErrorHandlingAspect())
            ->andRun(new StoreContextAspect($storeId))
            ->beforeEachMethodOfService(IntegrationController::class);
    }

    /**
     * @param string $storeId
     *
     * @return MerchantController
     *
     * @throws Exception
     */
    public function merchant(string $storeId): object
    {
        return Aspects
            ::run(new ErrorHandlingAspect())
            ->andRun(new StoreContextAspect($storeId))
            ->beforeEachMethodOfService(MerchantController::class);
    }

    /**
     * @param string $storeId
     *
     * @return ConnectionController
     */
    public function connection(string $storeId): object
    {
        return Aspects
            ::run(new ErrorHandlingAspect())
            ->andRun(new StoreContextAspect($storeId))
            ->beforeEachMethodOfService(ConnectionController::class);
    }

    /**
     * @param string $storeId
     *
     * @return StoreController
     */
    public function store(string $storeId): object
    {
        return Aspects
            ::run(new ErrorHandlingAspect())
            ->andRun(new StoreContextAspect($storeId))
            ->beforeEachMethodOfService(StoreController::class);
    }

    /**
     * @param string $storeId
     *
     * @return PaymentController
     */
    public function payment(string $storeId): object
    {
        return Aspects
            ::run(new ErrorHandlingAspect())
            ->andRun(new StoreContextAspect($storeId))
            ->beforeEachMethodOfService(PaymentController::class);
    }

    /**
     * @param string $storeId
     *
     * @return GeneralSettingsController
     */
    public function generalSettings(string $storeId): object
    {
        return Aspects
            ::run(new ErrorHandlingAspect())
            ->andRun(new StoreContextAspect($storeId))
            ->beforeEachMethodOfService(GeneralSettingsController::class);
    }

    /**
     * @param string $storeId
     *
     * @return AdyenGivingSettingsController
     */
    public function adyenGivingSettings(string $storeId): object
    {
        return Aspects
            ::run(new ErrorHandlingAspect())
            ->andRun(new StoreContextAspect($storeId))
            ->beforeEachMethodOfService(AdyenGivingSettingsController::class);
    }

    /**
     * @param string $storeId
     *
     * @return TestConnectionController
     */
    public function testConnection(string $storeId): object
    {
        return Aspects
            ::run(new ErrorHandlingAspect())
            ->andRun(new StoreContextAspect($storeId))
            ->beforeEachMethodOfService(TestConnectionController::class);
    }

    /**
     * @param string $storeId
     *
     * @return OrderMappingsController
     */
    public function orderMappings(string $storeId): object
    {
        return Aspects
            ::run(new ErrorHandlingAspect())
            ->andRun(new StoreContextAspect($storeId))
            ->beforeEachMethodOfService(OrderMappingsController::class);
    }

    /**
     * @param string $storeId
     *
     * @return WebhookNotificationController
     */
    public function webhookNotifications(string $storeId): object
    {
        return Aspects
            ::run(new ErrorHandlingAspect())
            ->andRun(new StoreContextAspect($storeId))
            ->beforeEachMethodOfService(WebhookNotificationController::class);
    }

    /**
     * @param string $storeId
     *
     * @return ShopNotificationsController
     */
    public function shopNotifications(string $storeId): object
    {
        return Aspects
            ::run(new ErrorHandlingAspect())
            ->andRun(new StoreContextAspect($storeId))
            ->beforeEachMethodOfService(ShopNotificationsController::class);
    }

    /**
     * @param string $storeId
     *
     * @return CaptureController
     */
    public function capture(string $storeId): object
    {
        return Aspects
            ::run(new ErrorHandlingAspect())
            ->andRun(new StoreContextAspect($storeId))
            ->beforeEachMethodOfService(CaptureController::class);
    }

    /**
     * @param string $storeId
     *
     * @return CancelController
     */
    public function cancel(string $storeId): object
    {
        return Aspects
            ::run(new ErrorHandlingAspect())
            ->andRun(new StoreContextAspect($storeId))
            ->beforeEachMethodOfService(CancelController::class);
    }

    /**
     * @param string $storeId
     *
     * @return RefundController
     */
    public function refund(string $storeId): object
    {
        return Aspects
            ::run(new ErrorHandlingAspect())
            ->andRun(new StoreContextAspect($storeId))
            ->beforeEachMethodOfService(RefundController::class);
    }

    /**
     * @return DebugController
     */
    public function debug(): object
    {
        return Aspects
            ::run(new ErrorHandlingAspect())
            ->beforeEachMethodOfService(DebugController::class);
    }

    /**
     * @param string $storeId
     *
     * @return WebhookValidationController
     */
    public function webhookValidation(string $storeId): object
    {
        return Aspects
            ::run(new ErrorHandlingAspect())
            ->andRun(new StoreContextAspect($storeId))
            ->beforeEachMethodOfService(WebhookValidationController::class);
    }

    /**
     * @return AutoTestController
     */
    public function autoTest(): object
    {
        return Aspects
            ::run(new ErrorHandlingAspect())
            ->beforeEachMethodOfService(AutoTestController::class);
    }

    /**
     * @return SystemInfoController
     */
    public function systemInfo(): object
    {
        return Aspects
            ::run(new ErrorHandlingAspect())
            ->beforeEachMethodOfService(SystemInfoController::class);
    }

    /**
     * @return VersionInfoController
     */
    public function versions(): object
    {
        return Aspects
            ::run(new ErrorHandlingAspect())
            ->beforeEachMethodOfService(VersionInfoController::class);
    }

    /**
     * @param string $storeId
     *
     * @return DisconnectController
     */
    public function disconnect(string $storeId): object
    {
        return Aspects
            ::run(new ErrorHandlingAspect())
            ->andRun(new StoreContextAspect($storeId))
            ->beforeEachMethodOfService(DisconnectController::class);
    }
}
