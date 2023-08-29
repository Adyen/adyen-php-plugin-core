<?php

namespace Adyen\Core\BusinessLogic;

use Adyen\Core\BusinessLogic\AdminAPI\AdyenGivingSettings\Controller\AdyenGivingSettingsController;
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
use Adyen\Core\BusinessLogic\AdminAPI\Merchant\Controller\MerchantController;
use Adyen\Core\BusinessLogic\AdminAPI\OrderMappings\Controller\OrderMappingsController;
use Adyen\Core\BusinessLogic\AdminAPI\Payment\Controller\PaymentController;
use Adyen\Core\BusinessLogic\AdminAPI\Refund\Controller\RefundController;
use Adyen\Core\BusinessLogic\AdminAPI\ShopNotifications\Controller\ShopNotificationsController;
use Adyen\Core\BusinessLogic\AdminAPI\Stores\Controller\StoreController;
use Adyen\Core\BusinessLogic\AdminAPI\TestConnection\Controller\TestConnectionController;
use Adyen\Core\BusinessLogic\AdminAPI\Versions\Controller\VersionInfoController;
use Adyen\Core\BusinessLogic\AdminAPI\WebhookNotifications\Controller\WebhookNotificationController;
use Adyen\Core\BusinessLogic\AdyenAPI;
use Adyen\Core\BusinessLogic\AdyenAPI\Checkout\Payments\Http\Proxy as PaymentsProxy;
use Adyen\Core\BusinessLogic\AdyenAPI\Management\Connection\Http\Proxy as ConnectionProxy;
use Adyen\Core\BusinessLogic\AdyenAPI\Management\Merchant\Http\Proxy as MerchantProxy;
use Adyen\Core\BusinessLogic\AdyenAPI\Management\Webhook\Http\Proxy;
use Adyen\Core\BusinessLogic\Bootstrap\SingleInstance;
use Adyen\Core\BusinessLogic\CheckoutAPI\CheckoutConfig\Controller\CheckoutConfigController;
use Adyen\Core\BusinessLogic\CheckoutAPI\Donations\Controller\DonationController;
use Adyen\Core\BusinessLogic\CheckoutAPI\PaymentRequest\Controller\PaymentRequestController;
use Adyen\Core\BusinessLogic\DataAccess\AdyenGiving\Entities\DonationsData;
use Adyen\Core\BusinessLogic\DataAccess\AdyenGivingSettings\Entities\AdyenGivingSettings;
use Adyen\Core\BusinessLogic\DataAccess\AdyenGivingSettings\Repositories\AdyenGivingSettingsRepository;
use Adyen\Core\BusinessLogic\DataAccess\Connection\Entities\ConnectionSettings;
use Adyen\Core\BusinessLogic\DataAccess\Connection\Repositories\ConnectionSettingsRepository;
use Adyen\Core\BusinessLogic\DataAccess\Disconnect\Entities\DisconnectTime;
use Adyen\Core\BusinessLogic\DataAccess\GeneralSettings\Entities\GeneralSettings;
use Adyen\Core\BusinessLogic\DataAccess\GeneralSettings\Repositories\GeneralSettingsRepository;
use Adyen\Core\BusinessLogic\DataAccess\Notifications\Entities\Notification;
use Adyen\Core\BusinessLogic\DataAccess\Notifications\Repositories\ShopNotificationRepository;
use Adyen\Core\BusinessLogic\DataAccess\OrderSettings\Entities\OrderStatusMapping;
use Adyen\Core\BusinessLogic\DataAccess\OrderSettings\Repositories\OrderStatusMappingRepository;
use Adyen\Core\BusinessLogic\DataAccess\Payment\Entities\PaymentMethod;
use Adyen\Core\BusinessLogic\DataAccess\TaskCleanup\Repositories\TaskCleanupRepository;
use Adyen\Core\BusinessLogic\DataAccess\TransactionHistory\Entities\TransactionHistory;
use Adyen\Core\BusinessLogic\DataAccess\TransactionHistory\Repositories\TransactionHistoryRepository;
use Adyen\Core\BusinessLogic\DataAccess\TransactionLog\Entities\TransactionLog;
use Adyen\Core\BusinessLogic\DataAccess\TransactionLog\Repositories\TransactionLogRepository;
use Adyen\Core\BusinessLogic\DataAccess\Webhook\Entities\WebhookConfig;
use Adyen\Core\BusinessLogic\Domain\AdyenGivingSettings\Repositories\AdyenGivingSettingsRepository as AdyenGivingSettingsRepositoryInterface;
use Adyen\Core\BusinessLogic\Domain\AdyenGivingSettings\Services\AdyenGivingSettingsService;
use Adyen\Core\BusinessLogic\Domain\Cancel\Handlers\CancelHandler;
use Adyen\Core\BusinessLogic\Domain\Cancel\Proxies\CancelProxy;
use Adyen\Core\BusinessLogic\Domain\Capture\Handlers\CaptureHandler;
use Adyen\Core\BusinessLogic\Domain\Capture\Proxies\CaptureProxy;
use Adyen\Core\BusinessLogic\Domain\Checkout\AdyenGiving\Proxies\DonationsProxy;
use Adyen\Core\BusinessLogic\Domain\Checkout\AdyenGiving\Repositories\DonationsDataRepository;
use Adyen\Core\BusinessLogic\Domain\Checkout\AdyenGiving\Services\DonationsService;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Factory\PaymentRequestFactory;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PaymentMethodCode;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors\AmountProcessor;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors\AuthenticationDataProcessor;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors\CaptureDelayHoursProcessor;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors\CaptureProcessor;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors\MerchantIdProcessor;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors\PaymentRequestProcessorsRegistry;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors\ReferenceProcessor;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors\ReturnUrlProcessor;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors\StateDataProcessors\BankAccountStateDataProcessor;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors\StateDataProcessors\BillingAddressStateDataProcessor;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors\StateDataProcessors\BrowserInfoStateDataProcessor;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors\StateDataProcessors\ConversionIdStateDataProcessor;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors\StateDataProcessors\CountryCodeStateDataProcessor;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors\StateDataProcessors\DateOfBirthStateDataProcessor;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors\StateDataProcessors\DeliveryAddressStateDataProcessor;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors\StateDataProcessors\InstallmentsStateDataProcessor;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors\StateDataProcessors\OriginStateDataProcessor;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors\StateDataProcessors\PaymentMethodStateDataProcessor;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors\StateDataProcessors\RiskDataStateDataProcessor;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors\StateDataProcessors\ShopperEmailStateDataProcessor;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors\StateDataProcessors\ShopperNameStateDataProcessor;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors\StateDataProcessors\SocialSecurityNumberStateDataProcessor;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors\StateDataProcessors\StorePaymentMethodStateDataProcessor;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors\StateDataProcessors\TelephoneNumberStateDataProcessor;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Proxies\PaymentsProxy as PaymentsProxyInterface;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Proxies\StoredDetailsProxy;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Services\PaymentCheckoutConfigService;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Services\PaymentRequestService;
use Adyen\Core\BusinessLogic\Domain\Connection\Proxies\ConnectionProxy as ConnectionProxyInterface;
use Adyen\Core\BusinessLogic\Domain\Connection\Repositories\ConnectionSettingsRepository as ConnectionSettingsRepositoryInterface;
use Adyen\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use Adyen\Core\BusinessLogic\Domain\Disconnect\Repositories\DisconnectRepository;
use Adyen\Core\BusinessLogic\Domain\Disconnect\Services\DisconnectService;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Repositories\GeneralSettingsRepository as GeneralSettingsRepositoryInterface;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Services\GeneralSettingsService;
use Adyen\Core\BusinessLogic\Domain\InfoSettings\Services\ValidationService;
use Adyen\Core\BusinessLogic\Domain\Integration\Order\OrderService;
use Adyen\Core\BusinessLogic\Domain\Integration\Payment\ShopPaymentService;
use Adyen\Core\BusinessLogic\Domain\Integration\Processors\AddressProcessor;
use Adyen\Core\BusinessLogic\Domain\Integration\Processors\BasketItemsProcessor;
use Adyen\Core\BusinessLogic\Domain\Integration\Processors\BirthdayProcessor;
use Adyen\Core\BusinessLogic\Domain\Integration\Processors\DeviceFingerprintProcessor;
use Adyen\Core\BusinessLogic\Domain\Integration\Processors\L2L3DataProcessor;
use Adyen\Core\BusinessLogic\Domain\Integration\Processors\LineItemsProcessor;
use Adyen\Core\BusinessLogic\Domain\Integration\Processors\ShopperEmailProcessor;
use Adyen\Core\BusinessLogic\Domain\Integration\Processors\ShopperLocaleProcessor;
use Adyen\Core\BusinessLogic\Domain\Integration\Processors\ShopperNameProcessor;
use Adyen\Core\BusinessLogic\Domain\Integration\Processors\ShopperReferenceProcessor;
use Adyen\Core\BusinessLogic\Domain\Integration\ShopNotifications\NullShopNotificationChannelAdapter;
use Adyen\Core\BusinessLogic\Domain\Integration\ShopNotifications\ShopNotificationChannelAdapter;
use Adyen\Core\BusinessLogic\Domain\Integration\Store\StoreService as IntegrationStoreService;
use Adyen\Core\BusinessLogic\Domain\Integration\SystemInfo\SystemInfoService;
use Adyen\Core\BusinessLogic\Domain\Integration\Version\VersionService;
use Adyen\Core\BusinessLogic\Domain\Integration\Webhook\WebhookUrlService;
use Adyen\Core\BusinessLogic\Domain\Merchant\Proxies\MerchantProxy as MerchantProxyInterface;
use Adyen\Core\BusinessLogic\Domain\Merchant\Services\MerchantService;
use Adyen\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Adyen\Core\BusinessLogic\Domain\NotificationsRemover\Listeners\NotificationsRemoverListener;
use Adyen\Core\BusinessLogic\Domain\Payment\Proxies\PaymentProxy;
use Adyen\Core\BusinessLogic\Domain\Payment\Repositories\PaymentMethodConfigRepository;
use Adyen\Core\BusinessLogic\Domain\Payment\Services\PaymentService;
use Adyen\Core\BusinessLogic\Domain\Refund\Handlers\RefundHandler;
use Adyen\Core\BusinessLogic\Domain\Refund\Proxies\RefundProxy;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Repositories\ShopNotificationRepository as ShopNotificationRepositoryInterface;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Services\ShopNotificationService;
use Adyen\Core\BusinessLogic\Domain\Stores\Services\StoreService;
use Adyen\Core\BusinessLogic\Domain\TaskCleanup\Interfaces\TaskCleanupRepository as TaskCleanupRepositoryInterface;
use Adyen\Core\BusinessLogic\Domain\TaskCleanup\Listeners\TaskCleanupListener;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Repositories\TransactionHistoryRepository as TransactionRepositoryInterface;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Services\TransactionDetailsService;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Services\TransactionHistoryService;
use Adyen\Core\BusinessLogic\Domain\Webhook\Proxies\WebhookProxy;
use Adyen\Core\BusinessLogic\Domain\Webhook\Repositories\WebhookConfigRepository;
use Adyen\Core\BusinessLogic\Domain\Webhook\Services\OrderStatusProvider;
use Adyen\Core\BusinessLogic\Domain\Webhook\Services\WebhookRegistrationService;
use Adyen\Core\BusinessLogic\Domain\Webhook\Services\WebhookSynchronizationService;
use Adyen\Core\BusinessLogic\TransactionLog\Listeners\AbortedListener;
use Adyen\Core\BusinessLogic\TransactionLog\Listeners\CreateListener;
use Adyen\Core\BusinessLogic\TransactionLog\Listeners\FailedListener;
use Adyen\Core\BusinessLogic\TransactionLog\Listeners\LoadListener;
use Adyen\Core\BusinessLogic\TransactionLog\Listeners\UpdateListener;
use Adyen\Core\BusinessLogic\TransactionLog\Repositories\TransactionLogRepository as TransactionLogRepositoryInterface;
use Adyen\Core\BusinessLogic\TransactionLog\Services\TransactionLogService;
use Adyen\Core\BusinessLogic\Webhook\Handler\WebhookHandler;
use Adyen\Core\BusinessLogic\Webhook\Repositories\OrderStatusMappingRepository as OrderStatusMappingRepositoryInterface;
use Adyen\Core\BusinessLogic\Webhook\Services\OrderStatusMappingService;
use Adyen\Core\BusinessLogic\Webhook\Validator\WebhookValidator;
use Adyen\Core\BusinessLogic\WebhookAPI\Controller\WebhookController;
use Adyen\Core\Infrastructure\AutoTest\AutoTestService;
use Adyen\Core\Infrastructure\BootstrapComponent as BaseBootstrapComponent;
use Adyen\Core\Infrastructure\Configuration\Configuration;
use Adyen\Core\Infrastructure\Logger\Interfaces\ShopLoggerAdapter;
use Adyen\Core\Infrastructure\ORM\RepositoryRegistry;
use Adyen\Core\Infrastructure\ServiceRegister;
use Adyen\Core\Infrastructure\TaskExecution\Events\BaseQueueItemEvent;
use Adyen\Core\Infrastructure\TaskExecution\Events\QueueItemAbortedEvent;
use Adyen\Core\Infrastructure\TaskExecution\Events\QueueItemEnqueuedEvent;
use Adyen\Core\Infrastructure\TaskExecution\Events\QueueItemFailedEvent;
use Adyen\Core\Infrastructure\TaskExecution\Events\QueueItemFinishedEvent;
use Adyen\Core\Infrastructure\TaskExecution\Events\QueueItemRequeuedEvent;
use Adyen\Core\Infrastructure\TaskExecution\Events\QueueItemStartedEvent;
use Adyen\Core\Infrastructure\TaskExecution\Events\QueueItemStateTransitionEventBus;
use Adyen\Core\Infrastructure\TaskExecution\QueueItem;
use Adyen\Core\Infrastructure\TaskExecution\QueueService;
use Adyen\Core\Infrastructure\TaskExecution\TaskEvents\TickEvent;
use Adyen\Core\Infrastructure\Utility\Events\EventBus;

/**
 * Class BootstrapComponent
 *
 * @package Adyen\Core\BusinessLogic
 */
class BootstrapComponent extends BaseBootstrapComponent
{
    /**
     * @return void
     */
    public static function init(): void
    {
        parent::init();

        static::initProxies();
        static::initControllers();
        static::initRepositories();
        static::initPaymentRequestProcessors();
    }

    /**
     * @return void
     */
    protected static function initServices(): void
    {
        parent::initServices();

        ServiceRegister::registerService(
            AdyenGivingSettingsService::class,
            new SingleInstance(static function () {
                return new AdyenGivingSettingsService(
                    ServiceRegister::getService(AdyenGivingSettingsRepositoryInterface::class)
                );
            })
        );

        ServiceRegister::registerService(StoreContext::class, static function () {
            return StoreContext::getInstance();
        });

        ServiceRegister::registerService(
            ConnectionService::class,
            static function () {
                return new ConnectionService(
                    ServiceRegister::getService(ConnectionSettingsRepositoryInterface::class),
                    ServiceRegister::getService(IntegrationStoreService::class),
                    ServiceRegister::getService(WebhookConfigRepository::class)
                );
            }
        );

        ServiceRegister::registerService(
            GeneralSettingsService::class,
            new SingleInstance(static function () {
                return new GeneralSettingsService(
                    ServiceRegister::getService(GeneralSettingsRepositoryInterface::class)
                );
            })
        );

        ServiceRegister::registerService(
            MerchantService::class,
            static function () {
                return new MerchantService(
                    ServiceRegister::getService(MerchantProxyInterface::class),
                    ServiceRegister::getService(ConnectionService::class)
                );
            }
        );

        ServiceRegister::registerService(
            StoreService::class,
            new SingleInstance(static function () {
                return new StoreService(
                    ServiceRegister::getService(IntegrationStoreService::class),
                    ServiceRegister::getService(ConnectionSettingsRepositoryInterface::class)
                );
            })
        );

        ServiceRegister::registerService(
            PaymentService::class,
            static function () {
                return new PaymentService(
                    ServiceRegister::getService(PaymentMethodConfigRepository::class),
                    ServiceRegister::getService(ConnectionSettingsRepositoryInterface::class),
                    ServiceRegister::getService(PaymentProxy::class),
                    ServiceRegister::getService(PaymentsProxyInterface::class)
                );
            }
        );

        ServiceRegister::registerService(
            PaymentRequestService::class,
            new SingleInstance(static function () {
                return new PaymentRequestService(
                    ServiceRegister::getService(PaymentsProxyInterface::class),
                    new PaymentRequestFactory(),
                    ServiceRegister::getService(DonationsDataRepository::class),
                    ServiceRegister::getService(TransactionHistoryService::class)
                );
            })
        );

        ServiceRegister::registerService(
            PaymentCheckoutConfigService::class,
            new SingleInstance(static function () {
                return new PaymentCheckoutConfigService(
                    ServiceRegister::getService(ConnectionSettingsRepositoryInterface::class),
                    ServiceRegister::getService(PaymentMethodConfigRepository::class),
                    ServiceRegister::getService(PaymentsProxyInterface::class),
                    ServiceRegister::getService(StoredDetailsProxy::class),
                    ServiceRegister::getService(ConnectionService::class)
                );
            })
        );

        ServiceRegister::registerService(
            OrderStatusMappingService::class,
            new SingleInstance(static function () {
                return new OrderStatusMappingService(
                    ServiceRegister::getService(OrderStatusMappingRepositoryInterface::class),
                    ServiceRegister::getService(IntegrationStoreService::class)
                );
            })
        );

        ServiceRegister::registerService(
            TransactionHistoryService::class,
            new SingleInstance(static function () {
                return new TransactionHistoryService(
                    ServiceRegister::getService(TransactionRepositoryInterface::class),
                    ServiceRegister::getService(GeneralSettingsRepositoryInterface::class)
                );
            })
        );

        ServiceRegister::registerService(
            WebhookRegistrationService::class,
            static function () {
                return new WebhookRegistrationService(
                    ServiceRegister::getService(WebhookProxy::class),
                    ServiceRegister::getService(MerchantProxyInterface::class),
                    ServiceRegister::getService(WebhookUrlService::class)
                );
            }
        );

        ServiceRegister::registerService(
            WebhookHandler::class,
            new SingleInstance(static function () {
                return new WebhookHandler(
                    ServiceRegister::getService(WebhookSynchronizationService::class),
                    ServiceRegister::getService(QueueService::class)
                );
            })
        );

        ServiceRegister::registerService(
            OrderStatusProvider::class,
            new SingleInstance(static function () {
                return ServiceRegister::getService(OrderStatusMappingService::class);
            })
        );

        ServiceRegister::registerService(
            WebhookSynchronizationService::class,
            new SingleInstance(static function () {
                return new WebhookSynchronizationService(
                    ServiceRegister::getService(TransactionHistoryService::class),
                    ServiceRegister::getService(OrderService::class),
                    ServiceRegister::getService(OrderStatusProvider::class)
                );
            })
        );

        ServiceRegister::registerService(
            WebhookValidator::class,
            new SingleInstance(static function () {
                return new WebhookValidator(
                    ServiceRegister::getService(WebhookConfigRepository::class)
                );
            })
        );

        ServiceRegister::registerService(
            DonationsService::class,
            new SingleInstance(static function () {
                return new DonationsService(
                    ServiceRegister::getService(DonationsProxy::class),
                    ServiceRegister::getService(AdyenGivingSettingsService::class),
                    ServiceRegister::getService(ConnectionService::class),
                    ServiceRegister::getService(DonationsDataRepository::class),
                    ServiceRegister::getService(OrderService::class),
                    ServiceRegister::getService(WebhookUrlService::class)
                );
            })
        );

        ServiceRegister::registerService(
            TransactionLogService::class,
            new SingleInstance(static function () {
                return new TransactionLogService(
                    ServiceRegister::getService(TransactionHistoryService::class),
                    ServiceRegister::getService(TransactionLogRepositoryInterface::class),
                    ServiceRegister::getService(OrderService::class),
                    ServiceRegister::getService(DisconnectRepository::class)
                );
            })
        );

        ServiceRegister::registerService(
            ShopNotificationChannelAdapter::class,
            new SingleInstance(static function () {
                return new NullShopNotificationChannelAdapter();
            })
        );

        ServiceRegister::registerService(
            ShopNotificationService::class,
            new SingleInstance(static function () {
                return new ShopNotificationService(
                    ServiceRegister::getService(ShopNotificationRepositoryInterface::class),
                    ServiceRegister::getService(ShopNotificationChannelAdapter::class),
                    ServiceRegister::getService(DisconnectRepository::class)
                );
            })
        );

        ServiceRegister::registerService(
            CaptureHandler::class,
            new SingleInstance(static function () {
                return new CaptureHandler(
                    ServiceRegister::getService(TransactionHistoryService::class),
                    ServiceRegister::getService(ShopNotificationService::class),
                    ServiceRegister::getService(CaptureProxy::class),
                    ServiceRegister::getService(ConnectionService::class)
                );
            })
        );

        ServiceRegister::registerService(
            CancelHandler::class,
            new SingleInstance(static function () {
                return new CancelHandler(
                    ServiceRegister::getService(TransactionHistoryService::class),
                    ServiceRegister::getService(ShopNotificationService::class),
                    ServiceRegister::getService(CancelProxy::class),
                    ServiceRegister::getService(ConnectionService::class)
                );
            })
        );

        ServiceRegister::registerService(
            RefundHandler::class,
            new SingleInstance(static function () {
                return new RefundHandler(
                    ServiceRegister::getService(TransactionHistoryService::class),
                    ServiceRegister::getService(ShopNotificationService::class),
                    ServiceRegister::getService(RefundProxy::class),
                    ServiceRegister::getService(ConnectionService::class)
                );
            })
        );

        ServiceRegister::registerService(
            TransactionDetailsService::class,
            new SingleInstance(static function () {
                return new TransactionDetailsService(
                    ServiceRegister::getService(ConnectionService::class),
                    ServiceRegister::getService(TransactionHistoryService::class)
                );
            })
        );

        ServiceRegister::registerService(
            ValidationService::class,
            static function () {
                return new ValidationService(
                    ServiceRegister::getService(WebhookProxy::class),
                    ServiceRegister::getService(WebhookConfigRepository::class)
                );
            }
        );

        ServiceRegister::registerService(
            AutoTestService::class,
            new SingleInstance(static function () {
                return new AutoTestService();
            })
        );

        ServiceRegister::registerService(
            DisconnectService::class,
            new SingleInstance(static function () {
                return new DisconnectService(
                    ServiceRegister::getService(WebhookConfigRepository::class),
                    ServiceRegister::getService(ConnectionSettingsRepositoryInterface::class),
                    ServiceRegister::getService(ShopPaymentService::class),
                    ServiceRegister::getService(QueueService::class),
                    ServiceRegister::getService(AdyenGivingSettingsRepositoryInterface::class),
                    ServiceRegister::getService(GeneralSettingsRepositoryInterface::class),
                    ServiceRegister::getService(OrderStatusMappingRepositoryInterface::class),
                    ServiceRegister::getService(PaymentMethodConfigRepository::class),
                    ServiceRegister::getService(DisconnectRepository::class)
                );
            })
        );

        ServiceRegister::registerService(
            DisconnectRepository::class,
            new SingleInstance(static function () {
                return new DataAccess\Disconnect\Repositories\DisconnectRepository(
                    ServiceRegister::getService(StoreContext::class),
                    RepositoryRegistry::getRepository(DisconnectTime::getClassName())
                );
            })
        );
    }

    /**
     * Initialize API facade controllers.
     *
     * @return void
     */
    protected static function initControllers(): void
    {
        ServiceRegister::registerService(
            AdyenGivingSettingsController::class,
            new SingleInstance(static function () {
                return new AdyenGivingSettingsController(
                    ServiceRegister::getService(AdyenGivingSettingsService::class)
                );
            })
        );

        ServiceRegister::registerService(
            MerchantController::class,
            new SingleInstance(static function () {
                return new MerchantController(ServiceRegister::getService(MerchantService::class));
            })
        );

        ServiceRegister::registerService(
            ConnectionController::class,
            new SingleInstance(static function () {
                return new ConnectionController(ServiceRegister::getService(ConnectionService::class));
            })
        );

        ServiceRegister::registerService(
            IntegrationController::class,
            new SingleInstance(static function () {
                return new IntegrationController(ServiceRegister::getService(ConnectionService::class));
            })
        );

        ServiceRegister::registerService(
            StoreController::class,
            new SingleInstance(static function () {
                return new StoreController(
                    ServiceRegister::getService(StoreService::class)
                );
            })
        );

        ServiceRegister::registerService(
            GeneralSettingsController::class,
            new SingleInstance(static function () {
                return new GeneralSettingsController(
                    ServiceRegister::getService(GeneralSettingsService::class)
                );
            })
        );

        ServiceRegister::registerService(
            PaymentController::class,
            static function () {
                return new PaymentController(
                    ServiceRegister::getService(PaymentService::class),
                    ServiceRegister::getService(ShopPaymentService::class)
                );
            }
        );

        ServiceRegister::registerService(
            PaymentRequestController::class,
            new SingleInstance(static function () {
                return new PaymentRequestController(
                    ServiceRegister::getService(PaymentRequestService::class)
                );
            })
        );

        ServiceRegister::registerService(
            CheckoutConfigController::class,
            new SingleInstance(static function () {
                return new CheckoutConfigController(
                    ServiceRegister::getService(PaymentCheckoutConfigService::class)
                );
            })
        );

        ServiceRegister::registerService(
            TestConnectionController::class,
            new SingleInstance(static function () {
                return new TestConnectionController(ServiceRegister::getService(ConnectionService::class));
            })
        );

        ServiceRegister::registerService(
            OrderMappingsController::class,
            new SingleInstance(static function () {
                return new OrderMappingsController(ServiceRegister::getService(OrderStatusMappingService::class));
            })
        );

        ServiceRegister::registerService(
            WebhookController::class,
            new SingleInstance(static function () {
                return new WebhookController(
                    ServiceRegister::getService(WebhookValidator::class),
                    ServiceRegister::getService(WebhookHandler::class)
                );
            })
        );

        ServiceRegister::registerService(
            DonationController::class,
            new SingleInstance(static function () {
                return new DonationController(
                    ServiceRegister::getService(DonationsService::class)
                );
            })
        );

        ServiceRegister::registerService(
            WebhookNotificationController::class,
            new SingleInstance(static function () {
                return new WebhookNotificationController(
                    ServiceRegister::getService(TransactionLogService::class)
                );
            })
        );

        ServiceRegister::registerService(
            ShopNotificationsController::class,
            new SingleInstance(static function () {
                return new ShopNotificationsController(
                    ServiceRegister::getService(ShopNotificationService::class)
                );
            })
        );

        ServiceRegister::registerService(
            CaptureController::class,
            new SingleInstance(static function () {
                return new CaptureController(
                    ServiceRegister::getService(CaptureHandler::class)
                );
            })
        );

        ServiceRegister::registerService(
            CancelController::class,
            new SingleInstance(static function () {
                return new CancelController(
                    ServiceRegister::getService(CancelHandler::class)
                );
            })
        );

        ServiceRegister::registerService(
            RefundController::class,
            new SingleInstance(static function () {
                return new RefundController(
                    ServiceRegister::getService(RefundHandler::class)
                );
            })
        );

        ServiceRegister::registerService(
            DebugController::class,
            new SingleInstance(static function () {
                return new DebugController(
                    ServiceRegister::getService(Configuration::CLASS_NAME)
                );
            })
        );

        ServiceRegister::registerService(
            WebhookValidationController::class,
            new SingleInstance(static function () {
                return new WebhookValidationController(
                    ServiceRegister::getService(ValidationService::class)
                );
            })
        );

        ServiceRegister::registerService(
            AutoTestController::class,
            new SingleInstance(static function () {
                return new AutoTestController(
                    ServiceRegister::getService(AutoTestService::class),
                    ServiceRegister::getService(ShopLoggerAdapter::CLASS_NAME)
                );
            })
        );

        ServiceRegister::registerService(
            SystemInfoController::class,
            new SingleInstance(static function () {
                return new SystemInfoController(
                    ServiceRegister::getService(SystemInfoService::class),
                    ServiceRegister::getService(PaymentMethodConfigRepository::class),
                    RepositoryRegistry::getQueueItemRepository(),
                    RepositoryRegistry::getRepository(ConnectionSettings::getClassName()),
                    ServiceRegister::getService(StoreService::class)
                );
            })
        );

        ServiceRegister::registerService(
            VersionInfoController::class,
            new SingleInstance(static function () {
                return new VersionInfoController(
                    ServiceRegister::getService(VersionService::class)
                );
            })
        );

        ServiceRegister::registerService(
            DisconnectController::class,
            new SingleInstance(static function () {
                return new DisconnectController(
                    ServiceRegister::getService(DisconnectService::class)
                );
            })
        );
    }

    /**
     * @return void
     */
    protected static function initProxies(): void
    {
        ServiceRegister::registerService(
            ConnectionProxyInterface::class,
            static function () {
                return AdyenAPI\Management\ProxyFactory::makeProxy(ConnectionProxy::class);
            }
        );

        ServiceRegister::registerService(
            MerchantProxyInterface::class,
            static function () {
                return AdyenAPI\Management\ProxyFactory::makeProxy(MerchantProxy::class);
            }
        );

        ServiceRegister::registerService(
            WebhookProxy::class,
            static function () {
                return AdyenAPI\Management\ProxyFactory::makeProxy(Proxy::class);
            }
        );

        ServiceRegister::registerService(
            PaymentsProxyInterface::class,
            static function () {
                return AdyenAPI\Checkout\ProxyFactory::makeProxy(PaymentsProxy::class);
            }
        );

        ServiceRegister::registerService(
            PaymentProxy::class,
            static function () {
                return AdyenAPI\Management\ProxyFactory::makeProxy(AdyenAPI\Management\Payment\Http\Proxy::class);
            }
        );

        ServiceRegister::registerService(
            StoredDetailsProxy::class,
            new SingleInstance(static function () {
                return AdyenAPI\Recurring\ProxyFactory::makeProxy(AdyenAPI\Recurring\StoredDetails\Http\Proxy::class);
            })
        );

        ServiceRegister::registerService(
            DonationsProxy::class,
            new SingleInstance(static function () {
                return AdyenAPI\Checkout\ProxyFactory::makeProxy(AdyenAPI\Checkout\Donations\Http\Proxy::class);
            })
        );

        ServiceRegister::registerService(
            CaptureProxy::class,
            new SingleInstance(static function () {
                return AdyenAPI\Checkout\ProxyFactory::makeProxy(AdyenAPI\Capture\Http\Proxy::class);
            })
        );

        ServiceRegister::registerService(
            CancelProxy::class,
            new SingleInstance(static function () {
                return AdyenAPI\Checkout\ProxyFactory::makeProxy(AdyenAPI\Cancel\Http\Proxy::class);
            })
        );

        ServiceRegister::registerService(
            RefundProxy::class,
            new SingleInstance(static function () {
                return AdyenAPI\Checkout\ProxyFactory::makeProxy(AdyenAPI\Refund\Http\Proxy::class);
            })
        );
    }

    /**
     * @return void
     */
    protected static function initRepositories(): void
    {
        parent::initRepositories();

        ServiceRegister::registerService(
            AdyenGivingSettingsRepositoryInterface::class,
            new SingleInstance(static function () {
                return new AdyenGivingSettingsRepository(
                    RepositoryRegistry::getRepository(AdyenGivingSettings::getClassName()),
                    ServiceRegister::getService(StoreContext::class)
                );
            })
        );

        ServiceRegister::registerService(
            ConnectionSettingsRepositoryInterface::class,
            new SingleInstance(static function () {
                return new ConnectionSettingsRepository(
                    RepositoryRegistry::getRepository(ConnectionSettings::getClassName()),
                    ServiceRegister::getService(StoreContext::class)
                );
            })
        );

        ServiceRegister::registerService(
            GeneralSettingsRepositoryInterface::class,
            new SingleInstance(static function () {
                return new GeneralSettingsRepository(
                    RepositoryRegistry::getRepository(GeneralSettings::getClassName()),
                    ServiceRegister::getService(StoreContext::class)
                );
            })
        );

        ServiceRegister::registerService(
            TransactionRepositoryInterface::class,
            new SingleInstance(static function () {
                return new TransactionHistoryRepository(
                    RepositoryRegistry::getRepository(TransactionHistory::getClassName()),
                    ServiceRegister::getService(StoreContext::class)
                );
            })
        );

        ServiceRegister::registerService(
            TransactionLogRepositoryInterface::class,
            new SingleInstance(static function () {
                return new TransactionLogRepository(
                    RepositoryRegistry::getRepository(TransactionLog::getClassName()),
                    ServiceRegister::getService(StoreContext::class)
                );
            })
        );

        ServiceRegister::registerService(
            WebhookConfigRepository::class,
            new SingleInstance(static function () {
                return new DataAccess\Webhook\Repositories\WebhookConfigRepository(
                    RepositoryRegistry::getRepository(WebhookConfig::getClassName()),
                    ServiceRegister::getService(StoreContext::class)
                );
            })
        );

        ServiceRegister::registerService(
            OrderStatusMappingRepositoryInterface::class,
            new SingleInstance(static function () {
                return new OrderStatusMappingRepository(
                    RepositoryRegistry::getRepository(OrderStatusMapping::getClassName()),
                    ServiceRegister::getService(StoreContext::class)
                );
            })
        );

        ServiceRegister::registerService(
            PaymentMethodConfigRepository::class,
            new SingleInstance(static function () {
                return new DataAccess\Payment\Repositories\PaymentMethodConfigRepository(
                    RepositoryRegistry::getRepository(PaymentMethod::getClassName()),
                    ServiceRegister::getService(StoreContext::class)
                );
            })
        );

        ServiceRegister::registerService(
            DonationsDataRepository::class,
            new SingleInstance(static function () {
                return new DataAccess\AdyenGiving\Repositories\DonationsDataRepository(
                    RepositoryRegistry::getRepository(DonationsData::getClassName()),
                    ServiceRegister::getService(StoreContext::class)
                );
            })
        );

        ServiceRegister::registerService(
            ShopNotificationRepositoryInterface::class,
            new SingleInstance(static function () {
                return new ShopNotificationRepository(
                    RepositoryRegistry::getRepository(Notification::getClassName()),
                    ServiceRegister::getService(StoreContext::class)
                );
            })
        );

        ServiceRegister::registerService(
            TaskCleanupRepositoryInterface::class,
            new SingleInstance(static function () {
                return new TaskCleanupRepository(
                    RepositoryRegistry::getRepository(QueueItem::getClassName())
                );
            })
        );
    }

    protected static function initPaymentRequestProcessors(): void
    {
        ServiceRegister::registerService(
            MerchantIdProcessor::class,
            new SingleInstance(static function () {
                return new MerchantIdProcessor(
                    ServiceRegister::getService(ConnectionSettingsRepositoryInterface::class)
                );
            })
        );
        ServiceRegister::registerService(
            AmountProcessor::class,
            new SingleInstance(static function () {
                return new AmountProcessor();
            })
        );
        ServiceRegister::registerService(
            ReferenceProcessor::class,
            new SingleInstance(static function () {
                return new ReferenceProcessor();
            })
        );
        ServiceRegister::registerService(
            ReturnUrlProcessor::class,
            new SingleInstance(static function () {
                return new ReturnUrlProcessor();
            })
        );
        ServiceRegister::registerService(
            PaymentMethodStateDataProcessor::class,
            new SingleInstance(static function () {
                return new PaymentMethodStateDataProcessor();
            })
        );

        ServiceRegister::registerService(
            BillingAddressStateDataProcessor::class,
            new SingleInstance(static function () {
                return new BillingAddressStateDataProcessor();
            })
        );

        ServiceRegister::registerService(
            BrowserInfoStateDataProcessor::class,
            new SingleInstance(static function () {
                return new BrowserInfoStateDataProcessor();
            })
        );

        ServiceRegister::registerService(
            ConversionIdStateDataProcessor::class,
            new SingleInstance(static function () {
                return new ConversionIdStateDataProcessor();
            })
        );

        ServiceRegister::registerService(
            CountryCodeStateDataProcessor::class,
            new SingleInstance(static function () {
                return new CountryCodeStateDataProcessor();
            })
        );

        ServiceRegister::registerService(
            DateOfBirthStateDataProcessor::class,
            new SingleInstance(static function () {
                return new DateOfBirthStateDataProcessor();
            })
        );

        ServiceRegister::registerService(
            DeliveryAddressStateDataProcessor::class,
            new SingleInstance(static function () {
                return new DeliveryAddressStateDataProcessor();
            })
        );

        ServiceRegister::registerService(
            InstallmentsStateDataProcessor::class,
            new SingleInstance(static function () {
                return new InstallmentsStateDataProcessor();
            })
        );

        ServiceRegister::registerService(
            OriginStateDataProcessor::class,
            new SingleInstance(static function () {
                return new OriginStateDataProcessor();
            })
        );

        ServiceRegister::registerService(
            RiskDataStateDataProcessor::class,
            new SingleInstance(static function () {
                return new RiskDataStateDataProcessor();
            })
        );

        ServiceRegister::registerService(
            ShopperEmailStateDataProcessor::class,
            new SingleInstance(static function () {
                return new ShopperEmailStateDataProcessor();
            })
        );

        ServiceRegister::registerService(
            ShopperNameStateDataProcessor::class,
            new SingleInstance(static function () {
                return new ShopperNameStateDataProcessor();
            })
        );

        ServiceRegister::registerService(
            SocialSecurityNumberStateDataProcessor::class,
            new SingleInstance(static function () {
                return new SocialSecurityNumberStateDataProcessor();
            })
        );

        ServiceRegister::registerService(
            StorePaymentMethodStateDataProcessor::class,
            new SingleInstance(static function () {
                return new StorePaymentMethodStateDataProcessor();
            })
        );

        ServiceRegister::registerService(
            TelephoneNumberStateDataProcessor::class,
            new SingleInstance(static function () {
                return new TelephoneNumberStateDataProcessor();
            })
        );

        ServiceRegister::registerService(
            BankAccountStateDataProcessor::class,
            new SingleInstance(static function () {
                return new BankAccountStateDataProcessor();
            })
        );

        ServiceRegister::registerService(
            CaptureDelayHoursProcessor::class,
            new SingleInstance(static function () {
                return new CaptureDelayHoursProcessor(
                    ServiceRegister::getService(GeneralSettingsService::class)
                );
            })
        );

        ServiceRegister::registerService(
            AuthenticationDataProcessor::class,
            new SingleInstance(static function () {
                return new AuthenticationDataProcessor();
            })
        );

        ServiceRegister::registerService(
            CaptureProcessor::class,
            new SingleInstance(static function () {
                return new CaptureProcessor(
                    ServiceRegister::getService(GeneralSettingsService::class)
                );
            })
        );

        PaymentRequestProcessorsRegistry::registerGlobal(MerchantIdProcessor::class);
        PaymentRequestProcessorsRegistry::registerGlobal(AmountProcessor::class);
        PaymentRequestProcessorsRegistry::registerGlobal(ReferenceProcessor::class);
        PaymentRequestProcessorsRegistry::registerGlobal(ReturnUrlProcessor::class);
        PaymentRequestProcessorsRegistry::registerGlobal(PaymentMethodStateDataProcessor::class);
        PaymentRequestProcessorsRegistry::registerGlobal(BillingAddressStateDataProcessor::class);
        PaymentRequestProcessorsRegistry::registerGlobal(BrowserInfoStateDataProcessor::class);
        PaymentRequestProcessorsRegistry::registerGlobal(ConversionIdStateDataProcessor::class);
        PaymentRequestProcessorsRegistry::registerGlobal(CountryCodeStateDataProcessor::class);
        PaymentRequestProcessorsRegistry::registerGlobal(DateOfBirthStateDataProcessor::class);
        PaymentRequestProcessorsRegistry::registerGlobal(DeliveryAddressStateDataProcessor::class);
        PaymentRequestProcessorsRegistry::registerGlobal(InstallmentsStateDataProcessor::class);
        PaymentRequestProcessorsRegistry::registerGlobal(OriginStateDataProcessor::class);
        PaymentRequestProcessorsRegistry::registerGlobal(RiskDataStateDataProcessor::class);
        PaymentRequestProcessorsRegistry::registerGlobal(ShopperEmailStateDataProcessor::class);
        PaymentRequestProcessorsRegistry::registerGlobal(ShopperNameStateDataProcessor::class);
        PaymentRequestProcessorsRegistry::registerGlobal(SocialSecurityNumberStateDataProcessor::class);
        PaymentRequestProcessorsRegistry::registerGlobal(StorePaymentMethodStateDataProcessor::class);
        PaymentRequestProcessorsRegistry::registerGlobal(TelephoneNumberStateDataProcessor::class);
        PaymentRequestProcessorsRegistry::registerGlobal(BankAccountStateDataProcessor::class);
        PaymentRequestProcessorsRegistry::registerGlobal(CaptureDelayHoursProcessor::class);
        PaymentRequestProcessorsRegistry::registerGlobal(BasketItemsProcessor::class);
        PaymentRequestProcessorsRegistry::registerGlobal(AddressProcessor::class);
        PaymentRequestProcessorsRegistry::registerGlobal(BirthdayProcessor::class);
        PaymentRequestProcessorsRegistry::registerGlobal(ShopperEmailProcessor::class);
        PaymentRequestProcessorsRegistry::registerGlobal(ShopperLocaleProcessor::class);
        PaymentRequestProcessorsRegistry::registerGlobal(ShopperNameProcessor::class);
        PaymentRequestProcessorsRegistry::registerGlobal(ShopperReferenceProcessor::class);
        PaymentRequestProcessorsRegistry::registerByPaymentType(
            PaymentMethodCode::facilyPay3x(),
            LineItemsProcessor::class
        );
        PaymentRequestProcessorsRegistry::registerByPaymentType(
            PaymentMethodCode::facilyPay4x(),
            LineItemsProcessor::class
        );
        PaymentRequestProcessorsRegistry::registerByPaymentType(
            PaymentMethodCode::facilyPay6x(),
            LineItemsProcessor::class
        );
        PaymentRequestProcessorsRegistry::registerByPaymentType(
            PaymentMethodCode::facilyPay10x(),
            LineItemsProcessor::class
        );
        PaymentRequestProcessorsRegistry::registerByPaymentType(
            PaymentMethodCode::facilyPay12x(),
            LineItemsProcessor::class
        );
        PaymentRequestProcessorsRegistry::registerByPaymentType(
            PaymentMethodCode::afterPayTouch(),
            LineItemsProcessor::class
        );
        PaymentRequestProcessorsRegistry::registerByPaymentType(
            PaymentMethodCode::clearPay(),
            LineItemsProcessor::class
        );
        PaymentRequestProcessorsRegistry::registerByPaymentType(PaymentMethodCode::klarna(), LineItemsProcessor::class);
        PaymentRequestProcessorsRegistry::registerByPaymentType(PaymentMethodCode::klarnaPayNow(),
            LineItemsProcessor::class);
        PaymentRequestProcessorsRegistry::registerByPaymentType(PaymentMethodCode::klarnaAccount(),
            LineItemsProcessor::class);
        PaymentRequestProcessorsRegistry::registerByPaymentType(PaymentMethodCode::zip(), LineItemsProcessor::class);
        PaymentRequestProcessorsRegistry::registerByPaymentType(
            PaymentMethodCode::scheme(),
            AuthenticationDataProcessor::class
        );
        PaymentRequestProcessorsRegistry::registerByPaymentType(
            PaymentMethodCode::scheme(),
            L2L3DataProcessor::class
        );

        PaymentRequestProcessorsRegistry::registerGlobal(CaptureProcessor::class);
    }

    /**
     * @return void
     */
    protected static function initEvents(): void
    {
        parent::initEvents();

        /** @var QueueItemStateTransitionEventBus $queueBus */
        $queueBus = ServiceRegister::getService(QueueItemStateTransitionEventBus::CLASS_NAME);
        /** @var EventBus $eventBus */
        $eventBus = ServiceRegister::getService(EventBus::CLASS_NAME);

        $queueBus->when(
            QueueItemEnqueuedEvent::class,
            static function (BaseQueueItemEvent $event) {
                (new CreateListener(ServiceRegister::getService(TransactionLogService::class)))->handle($event);
            }
        );

        $queueBus->when(
            QueueItemRequeuedEvent::class,
            static function (BaseQueueItemEvent $event) {
                (new CreateListener(ServiceRegister::getService(TransactionLogService::class)))->handle($event);
            }
        );

        $queueBus->when(
            QueueItemStartedEvent::class,
            static function (BaseQueueItemEvent $event) {
                (new LoadListener(ServiceRegister::getService(TransactionLogService::class)))->handle($event);
            }
        );

        $queueBus->when(
            QueueItemStartedEvent::class,
            static function (BaseQueueItemEvent $event) {
                (new UpdateListener(ServiceRegister::getService(TransactionLogService::class)))->handle($event);
            }
        );

        $queueBus->when(
            QueueItemFinishedEvent::class,
            static function (BaseQueueItemEvent $event) {
                (new UpdateListener(ServiceRegister::getService(TransactionLogService::class)))->handle($event);
            }
        );

        $queueBus->when(
            QueueItemFailedEvent::class,
            static function (BaseQueueItemEvent $event) {
                (new FailedListener(ServiceRegister::getService(TransactionLogService::class)))->handle($event);
            }
        );

        $queueBus->when(
            QueueItemAbortedEvent::class,
            static function (BaseQueueItemEvent $event) {
                (new AbortedListener(ServiceRegister::getService(TransactionLogService::class)))->handle($event);
            }
        );

        $eventBus->when(
            TickEvent::class,
            static function () {
                (new NotificationsRemoverListener())->handle();
                (new TaskCleanupListener())->handle();
            }
        );
    }
}
