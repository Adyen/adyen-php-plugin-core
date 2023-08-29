<?php

namespace Adyen\Core\Tests\BusinessLogic\Common;

use Adyen\Core\BusinessLogic\AdminAPI\InfoSettings\Controller\AutoTestController;
use Adyen\Core\BusinessLogic\AdminAPI\InfoSettings\Controller\DebugController;
use Adyen\Core\BusinessLogic\AdminAPI\InfoSettings\Controller\SystemInfoController;
use Adyen\Core\BusinessLogic\AdminAPI\InfoSettings\Controller\WebhookValidationController;
use Adyen\Core\BusinessLogic\AdminAPI\Payment\Controller\PaymentController;
use Adyen\Core\BusinessLogic\AdminAPI\ShopNotifications\Controller\ShopNotificationsController;
use Adyen\Core\BusinessLogic\AdminAPI\Versions\Controller\VersionInfoController;
use Adyen\Core\BusinessLogic\AdminAPI\WebhookNotifications\Controller\WebhookNotificationController;
use Adyen\Core\BusinessLogic\AdyenAPI\Management\Connection\Http\Proxy;
use Adyen\Core\BusinessLogic\AdyenAPI\Management\ProxyFactory;
use Adyen\Core\BusinessLogic\Bootstrap\SingleInstance;
use Adyen\Core\BusinessLogic\DataAccess\AdyenGiving\Entities\DonationsData;
use Adyen\Core\BusinessLogic\DataAccess\AdyenGivingSettings\Entities\AdyenGivingSettings as AdyenGivingSettingsEntity;
use Adyen\Core\BusinessLogic\DataAccess\AdyenGivingSettings\Repositories\AdyenGivingSettingsRepository;
use Adyen\Core\BusinessLogic\DataAccess\Connection\Entities\ConnectionSettings;
use Adyen\Core\BusinessLogic\DataAccess\Connection\Entities\ConnectionSettings as ConnectionSettingsEntity;
use Adyen\Core\BusinessLogic\DataAccess\Disconnect\Entities\DisconnectTime;
use Adyen\Core\BusinessLogic\DataAccess\GeneralSettings\Entities\GeneralSettings;
use Adyen\Core\BusinessLogic\DataAccess\GeneralSettings\Entities\GeneralSettings as GeneralSettingsEntity;
use Adyen\Core\BusinessLogic\DataAccess\GeneralSettings\Repositories\GeneralSettingsRepository;
use Adyen\Core\BusinessLogic\DataAccess\Notifications\Entities\Notification as NotificationEntity;
use Adyen\Core\BusinessLogic\DataAccess\Notifications\Repositories\ShopNotificationRepository;
use Adyen\Core\BusinessLogic\DataAccess\OrderSettings\Entities\OrderStatusMapping as OrderStatusMappingSettingsEntity;
use Adyen\Core\BusinessLogic\DataAccess\OrderSettings\Repositories\OrderStatusMappingRepository;
use Adyen\Core\BusinessLogic\DataAccess\Payment\Entities\PaymentMethod;
use Adyen\Core\BusinessLogic\DataAccess\TaskCleanup\Repositories\TaskCleanupRepository;
use Adyen\Core\BusinessLogic\DataAccess\TransactionHistory\Entities\TransactionHistory as TransactionEntity;
use Adyen\Core\BusinessLogic\DataAccess\TransactionHistory\Repositories\TransactionHistoryRepository;
use Adyen\Core\BusinessLogic\DataAccess\TransactionLog\Entities\TransactionLog as TransactionLogEntity;
use Adyen\Core\BusinessLogic\DataAccess\TransactionLog\Repositories\TransactionLogRepository;
use Adyen\Core\BusinessLogic\DataAccess\Webhook\Entities\WebhookConfig;
use Adyen\Core\BusinessLogic\Domain\AdyenGivingSettings\Repositories\AdyenGivingSettingsRepository as AdyenGivingSettingsRepositoryInterface;
use Adyen\Core\BusinessLogic\Domain\AdyenGivingSettings\Services\AdyenGivingSettingsService;
use Adyen\Core\BusinessLogic\Domain\Cancel\Handlers\CancelHandler;
use Adyen\Core\BusinessLogic\Domain\Cancel\Proxies\CancelProxy;
use Adyen\Core\BusinessLogic\Domain\Capture\Handlers\CaptureHandler;
use Adyen\Core\BusinessLogic\Domain\Capture\Proxies\CaptureProxy;
use Adyen\Core\BusinessLogic\Domain\Checkout\AdyenGiving\Repositories\DonationsDataRepository;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Proxies\PaymentsProxy;
use Adyen\Core\BusinessLogic\Domain\Connection\Proxies\ConnectionProxy;
use Adyen\Core\BusinessLogic\Domain\Connection\Repositories\ConnectionSettingsRepository;
use Adyen\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use Adyen\Core\BusinessLogic\Domain\Disconnect\Repositories\DisconnectRepository;
use Adyen\Core\BusinessLogic\Domain\Disconnect\Services\DisconnectService;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Repositories\GeneralSettingsRepository as GeneralSettingsRepositoryInterface;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Services\GeneralSettingsService;
use Adyen\Core\BusinessLogic\Domain\InfoSettings\Services\ValidationService;
use Adyen\Core\BusinessLogic\Domain\Integration\Order\OrderService;
use Adyen\Core\BusinessLogic\Domain\Integration\Payment\ShopPaymentService;
use Adyen\Core\BusinessLogic\Domain\Integration\ShopNotifications\NullShopNotificationChannelAdapter;
use Adyen\Core\BusinessLogic\Domain\Integration\ShopNotifications\ShopNotificationChannelAdapter;
use Adyen\Core\BusinessLogic\Domain\Integration\Store\StoreService;
use Adyen\Core\BusinessLogic\Domain\Integration\SystemInfo\SystemInfoService;
use Adyen\Core\BusinessLogic\Domain\Integration\Version\VersionService;
use Adyen\Core\BusinessLogic\Domain\Integration\Webhook\WebhookUrlService;
use Adyen\Core\BusinessLogic\Domain\Merchant\Proxies\MerchantProxy;
use Adyen\Core\BusinessLogic\Domain\Merchant\Services\MerchantService;
use Adyen\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Adyen\Core\BusinessLogic\Domain\Payment\Proxies\PaymentProxy;
use Adyen\Core\BusinessLogic\Domain\Payment\Repositories\PaymentMethodConfigRepository;
use Adyen\Core\BusinessLogic\Domain\Payment\Services\PaymentService;
use Adyen\Core\BusinessLogic\Domain\Refund\Handlers\RefundHandler;
use Adyen\Core\BusinessLogic\Domain\Refund\Proxies\RefundProxy;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Repositories\ShopNotificationRepository as ShopNotificationRepositoryInterface;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Services\ShopNotificationService;
use Adyen\Core\BusinessLogic\Domain\Stores\Services\StoreService as DomainStoreService;
use Adyen\Core\BusinessLogic\Domain\TaskCleanup\Interfaces\TaskCleanupRepository as TaskCleanupRepositoryInterface;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Repositories\TransactionHistoryRepository as TransactionRepositoryInterface;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Services\TransactionHistoryService;
use Adyen\Core\BusinessLogic\Domain\Webhook\Proxies\WebhookProxy;
use Adyen\Core\BusinessLogic\Domain\Webhook\Repositories\WebhookConfigRepository;
use Adyen\Core\BusinessLogic\Domain\Webhook\Services\WebhookRegistrationService;
use Adyen\Core\BusinessLogic\Domain\Webhook\Services\WebhookSynchronizationService;
use Adyen\Core\BusinessLogic\Domain\Webhook\Services\WebhookSynchronizationService as WebhookSynchronizationServiceInterface;
use Adyen\Core\BusinessLogic\TransactionLog\Repositories\TransactionLogRepository as TransactionLogRepositoryInterface;
use Adyen\Core\BusinessLogic\TransactionLog\Services\TransactionLogService;
use Adyen\Core\BusinessLogic\Webhook\Handler\WebhookHandler;
use Adyen\Core\BusinessLogic\Webhook\Repositories\OrderStatusMappingRepository as OrderStatusMappingRepositoryInterface;
use Adyen\Core\BusinessLogic\Webhook\Services\OrderStatusMappingService;
use Adyen\Core\BusinessLogic\Webhook\Validator\WebhookValidator;
use Adyen\Core\BusinessLogic\WebhookAPI\Controller\WebhookController;
use Adyen\Core\Infrastructure\AutoTest\AutoTestService;
use Adyen\Core\Infrastructure\Configuration\ConfigEntity;
use Adyen\Core\Infrastructure\Configuration\Configuration;
use Adyen\Core\Infrastructure\Http\HttpClient;
use Adyen\Core\Infrastructure\Logger\Interfaces\ShopLoggerAdapter;
use Adyen\Core\Infrastructure\Logger\Logger;
use Adyen\Core\Infrastructure\Logger\LoggerConfiguration;
use Adyen\Core\Infrastructure\ORM\RepositoryRegistry;
use Adyen\Core\Infrastructure\Serializer\Concrete\JsonSerializer;
use Adyen\Core\Infrastructure\Serializer\Serializer;
use Adyen\Core\Infrastructure\TaskExecution\Events\QueueItemStateTransitionEventBus;
use Adyen\Core\Infrastructure\TaskExecution\Interfaces\TaskRunnerWakeup;
use Adyen\Core\Infrastructure\TaskExecution\QueueItem;
use Adyen\Core\Infrastructure\TaskExecution\QueueService;
use Adyen\Core\Infrastructure\Utility\Events\EventBus;
use Adyen\Core\Infrastructure\Utility\GuidProvider;
use Adyen\Core\Infrastructure\Utility\TimeProvider;
use Adyen\Core\Tests\BusinessLogic\AdminAPI\ShopNotifications\MockComponents\MockShopNotificationsRepository;
use Adyen\Core\Tests\BusinessLogic\AdminAPI\Versions\MockComponents\MockVersionService;
use Adyen\Core\Tests\BusinessLogic\AdminAPI\WebhookNotifications\MockComponents\MockShopLogRepository;
use Adyen\Core\Tests\BusinessLogic\Common\MockComponents\MemoryRepositoryWithConditionalDelete;
use Adyen\Core\Tests\BusinessLogic\Common\MockComponents\MockComponents\MockAdyenGivingRepository;
use Adyen\Core\Tests\BusinessLogic\Common\MockComponents\MockComponents\MockPaymentRepository;
use Adyen\Core\Tests\BusinessLogic\Common\MockComponents\MockShopPaymentService;
use Adyen\Core\Tests\BusinessLogic\Common\MockComponents\MockStoreService;
use Adyen\Core\Tests\BusinessLogic\Domain\TaskCleanup\Mocks\QueueItemRepository;
use Adyen\Core\Tests\BusinessLogic\Domain\Webhook\Mocks\MockWebhookUrlService;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\Logger\TestShopLogger;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\ORM\MemoryRepository;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\ORM\MemoryStorage;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\TestQueueService;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\TestTaskRunnerWakeupService;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\TestHttpClient;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\Utility\Events\TestEventEmitter;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\Utility\TestGuidProvider;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\Utility\TestTimeProvider;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;
use PHPUnit\Framework\TestCase;

class BaseTestCase extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();

        TestServiceRegister::getInstance();
        new TestServiceRegister([
            Configuration::class => function () {
                return MockComponents\Configuration::getInstance();
            },
            Serializer::class => function () {
                return new JsonSerializer();
            },
            QueueService::class => function () {
                return new TestQueueService();
            },
            EventBus::class => function () {
                return TestEventEmitter::getInstance();
            },
            TaskRunnerWakeup::class => function () {
                return new TestTaskRunnerWakeupService();
            },
            QueueItemStateTransitionEventBus::CLASS_NAME => function () {
                return QueueItemStateTransitionEventBus::getInstance();
            },
            ShopLoggerAdapter::class => function () {
                return new TestShopLogger();
            },
            StoreContext::class => function () {
                return StoreContext::getInstance();
            },
            ConnectionSettingsRepository::class => function () {
                return new \Adyen\Core\BusinessLogic\DataAccess\Connection\Repositories\ConnectionSettingsRepository(
                    TestRepositoryRegistry::getRepository(ConnectionSettings::getClassName()),
                    StoreContext::getInstance()
                );
            },
            GeneralSettingsRepositoryInterface::class => function () {
                return new GeneralSettingsRepository(
                    TestRepositoryRegistry::getRepository(GeneralSettingsEntity::getClassName()),
                    StoreContext::getInstance()
                );
            },
            TaskCleanupRepositoryInterface::class => function () {
                return new TaskCleanupRepository(
                    TestRepositoryRegistry::getRepository(QueueItem::getClassName())
                );
            },
            TransactionRepositoryInterface::class => function () {
                return new TransactionHistoryRepository(
                    TestRepositoryRegistry::getRepository(TransactionEntity::getClassName()),
                    StoreContext::getInstance()
                );
            },

            TransactionLogRepositoryInterface::class => function () {
                return new TransactionLogRepository(
                    TestRepositoryRegistry::getRepository(TransactionLogEntity::getClassName()),
                    StoreContext::getInstance()
                );
            },
            OrderStatusMappingRepositoryInterface::class => function () {
                return new OrderStatusMappingRepository(
                    TestRepositoryRegistry::getRepository(OrderStatusMappingSettingsEntity::getClassName()),
                    StoreContext::getInstance()
                );
            },
            AdyenGivingSettingsRepositoryInterface::class => function () {
                return new AdyenGivingSettingsRepository(
                    TestRepositoryRegistry::getRepository(AdyenGivingSettingsEntity::getClassName()),
                    StoreContext::getInstance()
                );
            },
            ShopNotificationRepositoryInterface::class => function () {
                return new ShopNotificationRepository(
                    TestRepositoryRegistry::getRepository(NotificationEntity::getClassName()),
                    StoreContext::getInstance()
                );
            },
            Proxy::class => new SingleInstance(static function () {
                return ProxyFactory::makeProxy(Proxy::class);
            }),
            HttpClient::class => function () {
                return new TestHttpClient();
            },
            ConnectionService::class => static function () {
                return new ConnectionService(
                    new \Adyen\Core\BusinessLogic\DataAccess\Connection\Repositories\ConnectionSettingsRepository(
                        TestRepositoryRegistry::getRepository(ConnectionSettingsEntity::getClassName()),
                        TestServiceRegister::getService(StoreContext::class)
                    ),
                    TestServiceRegister::getService(StoreService::class),
                    TestServiceRegister::getService(WebhookConfigRepository::class)
                );
            },
            ConnectionProxy::class => function () {
                return new Proxy(
                    TestServiceRegister::getService(HttpClient::class),
                    'test.url', 'V1', '0123456789'
                );
            },

            GeneralSettingsService::class => static function () {
                return new GeneralSettingsService(
                    TestServiceRegister::getService(GeneralSettingsRepositoryInterface::class)
                );
            },
            OrderStatusMappingService::class => static function () {
                return new  OrderStatusMappingService(
                    TestServiceRegister::getService(OrderStatusMappingRepositoryInterface::class),
                    TestServiceRegister::getService(StoreService::class)
                );
            },
            AdyenGivingSettingsService::class => static function () {
                return new AdyenGivingSettingsService(
                    TestServiceRegister::getService(AdyenGivingSettingsRepositoryInterface::class)
                );
            },

            TransactionHistoryService::class => static function () {
                return new TransactionHistoryService(
                    TestServiceRegister::getService(TransactionRepositoryInterface::class),
                    TestServiceRegister::getService(GeneralSettingsRepositoryInterface::class)
                );
            },
            TransactionLogService::class => static function () {
                return new TransactionLogService(
                    TestServiceRegister::getService(TransactionHistoryService::class),
                    TestServiceRegister::getService(TransactionLogRepositoryInterface::class),
                    TestServiceRegister::getService(OrderService::class),
                    TestServiceRegister::getService(DisconnectRepository::class)
                );
            },
            MerchantService::class => function () {
                return new MerchantService(
                    TestServiceRegister::getService(MerchantProxy::class),
                    TestServiceRegister::getService(ConnectionService::class)
                );
            },
            MerchantProxy::class => function () {
                return new \Adyen\Core\BusinessLogic\AdyenAPI\Management\Merchant\Http\Proxy(
                    TestServiceRegister::getService(HttpClient::class),
                    'test.url', 'V1', '0123456789'
                );
            },
            WebhookUrlService::class => function () {
                return new MockWebhookUrlService();
            },
            WebhookRegistrationService::class => function () {
                return new WebhookRegistrationService(
                    TestServiceRegister::getService(WebhookProxy::class),
                    TestServiceRegister::getService(MerchantProxy::class),
                    TestServiceRegister::getService(WebhookUrlService::class)
                );
            },
            WebhookConfigRepository::class => function () {
                return new \Adyen\Core\BusinessLogic\DataAccess\Webhook\Repositories\WebhookConfigRepository(
                    TestRepositoryRegistry::getRepository(WebhookConfig::getClassName()),
                    StoreContext::getInstance()
                );
            },
            WebhookProxy::class => function () {
                return new \Adyen\Core\BusinessLogic\AdyenAPI\Management\Webhook\Http\Proxy(
                    TestServiceRegister::getService(HttpClient::class), 'test.url', 'V1', '0123456789'
                );
            },
            StoreService::class => function () {
                return new MockStoreService();
            },
            PaymentMethodConfigRepository::class => function () {
                return new \Adyen\Core\BusinessLogic\DataAccess\Payment\Repositories\PaymentMethodConfigRepository(
                    TestRepositoryRegistry::getRepository(PaymentMethod::getClassName()),
                    StoreContext::getInstance()
                );
            },
            PaymentService::class => function () {
                return new PaymentService(
                    TestServiceRegister::getService(PaymentMethodConfigRepository::class),
                    TestServiceRegister::getService(ConnectionSettingsRepository::class),
                    TestServiceRegister::getService(PaymentProxy::class),
                    TestServiceRegister::getService(PaymentsProxy::class)
                );
            },
            ShopNotificationService::class => function () {
                return new ShopNotificationService(
                    TestServiceRegister::getService(ShopNotificationRepositoryInterface::class),
                    new NullShopNotificationChannelAdapter(),
                    TestServiceRegister::getService(DisconnectRepository::class)
                );
            },
            ShopPaymentService::class => function () {
                return new MockShopPaymentService();
            },
            PaymentController::class => function () {
                return new PaymentController(
                    TestServiceRegister::getService(PaymentService::class),
                    TestServiceRegister::getService(ShopPaymentService::class)
                );
            },
            WebhookController::class => function () {
                return new WebhookController(
                    TestServiceRegister::getService(WebhookValidator::class),
                    TestServiceRegister::getService(WebhookHandler::class)
                );
            },
            DebugController::class => function () {
                return new DebugController(
                    TestServiceRegister::getService(Configuration::class)
                );
            },
            SystemInfoController::class => function () {
                return new SystemInfoController(
                    TestServiceRegister::getService(SystemInfoService::class),
                    TestServiceRegister::getService(PaymentMethodConfigRepository::class),
                    RepositoryRegistry::getQueueItemRepository(),
                    TestRepositoryRegistry::getRepository(ConnectionSettings::getClassName()),
                    TestServiceRegister::getService(DomainStoreService::class)
                );
            },
            DomainStoreService::class => function () {
                return new DomainStoreService(
                    TestServiceRegister::getService(StoreService::class),
                    TestServiceRegister::getService(ConnectionSettingsRepository::class)
                );
            },
            AutoTestController::class => function () {
                return new AutoTestController(
                    TestServiceRegister::getService(AutoTestService::class),
                    TestServiceRegister::getService(ShopLoggerAdapter::class)
                );
            },
            WebhookValidationController::class => function () {
                return new WebhookValidationController(
                    TestServiceRegister::getService(ValidationService::class)
                );
            },
            WebhookNotificationController::class => function () {
                return new WebhookNotificationController(
                    TestServiceRegister::getService(TransactionLogService::class)
                );
            },
            ShopNotificationsController::class => function () {
                return new ShopNotificationsController(
                    TestServiceRegister::getService(ShopNotificationService::class)
                );
            },
            PaymentProxy::class => function () {
                return new \Adyen\Core\BusinessLogic\AdyenAPI\Management\Payment\Http\Proxy(
                    TestServiceRegister::getService(HttpClient::class), 'test.url', 'V1', '0123456789'
                );
            },
            PaymentsProxy::class => function () {
                return new \Adyen\Core\BusinessLogic\AdyenAPI\Checkout\Payments\Http\Proxy(
                    TestServiceRegister::getService(HttpClient::class), 'test.url', 'V1', '0123456789'
                );
            },
            OrderService::class => function () {
                return new MockComponents\MockOrderService();
            },
            DonationsDataRepository::class => function () {
                return new \Adyen\Core\BusinessLogic\DataAccess\AdyenGiving\Repositories\DonationsDataRepository(
                    TestRepositoryRegistry::getRepository(DonationsData::getClassName()),
                    StoreContext::getInstance()
                );
            },
            VersionService::class => function () {
                return new MockVersionService();
            },
            VersionInfoController::class => function () {
                return new VersionInfoController(TestServiceRegister::getService(VersionService::class));
            },
            ShopNotificationChannelAdapter::class => function () {
                return new NullShopNotificationChannelAdapter();
            },
            DisconnectService::class => function () {
                return new DisconnectService(
                    TestServiceRegister::getService(WebhookConfigRepository::class),
                    TestServiceRegister::getService(ConnectionSettingsRepository::class),
                    TestServiceRegister::getService(ShopPaymentService::class),
                    TestServiceRegister::getService(QueueService::class),
                    TestServiceRegister::getService(AdyenGivingSettingsRepositoryInterface::class),
                    TestServiceRegister::getService(GeneralSettingsRepositoryInterface::class),
                    TestServiceRegister::getService(OrderStatusMappingRepositoryInterface::class),
                    TestServiceRegister::getService(PaymentMethodConfigRepository::class),
                    TestServiceRegister::getService(DisconnectRepository::class)
                );
            },
            DisconnectRepository::class => function () {
                return new \Adyen\Core\BusinessLogic\DataAccess\Disconnect\Repositories\DisconnectRepository(
                    TestServiceRegister::getService(StoreContext::class),
                    TestRepositoryRegistry::getRepository(DisconnectTime::getClassName())
                );
            }
        ]);

        TestServiceRegister::registerService(
            TimeProvider::class,
            function () {
                return TestTimeProvider::getInstance();
            }
        );

        TestServiceRegister::registerService(
            WebhookHandler::class,
            static function () {
                return new WebhookHandler(
                    TestServiceRegister::getService(WebhookSynchronizationServiceInterface::class),
                    TestServiceRegister::getService(QueueService::class)
                );
            }
        );

        TestServiceRegister::registerService(
            CaptureHandler::class,
            static function () {
                return new CaptureHandler(
                    TestServiceRegister::getService(TransactionHistoryService::class),
                    TestServiceRegister::getService(ShopNotificationService::class),
                    TestServiceRegister::getService(CaptureProxy::class),
                    TestServiceRegister::getService(ConnectionService::class)
                );
            }
        );

        TestServiceRegister::registerService(
            CancelHandler::class,
            static function () {
                return new CancelHandler(
                    TestServiceRegister::getService(TransactionHistoryService::class),
                    TestServiceRegister::getService(ShopNotificationService::class),
                    TestServiceRegister::getService(CancelProxy::class),
                    TestServiceRegister::getService(ConnectionService::class)
                );
            }
        );

        TestServiceRegister::registerService(
            RefundHandler::class,
            static function () {
                return new RefundHandler(
                    TestServiceRegister::getService(TransactionHistoryService::class),
                    TestServiceRegister::getService(ShopNotificationService::class),
                    TestServiceRegister::getService(RefundProxy::class),
                    TestServiceRegister::getService(ConnectionService::class)
                );
            }
        );

        TestServiceRegister::registerService(
            WebhookSynchronizationServiceInterface::class,
            static function () {
                return new WebhookSynchronizationService(
                    TestServiceRegister::getService(TransactionHistoryService::class),
                    TestServiceRegister::getService(OrderService::class),
                    TestServiceRegister::getService(OrderStatusMappingService::class)
                );
            }
        );

        TestServiceRegister::registerService(
            ValidationService::class,
            static function () {
                return new ValidationService(
                    TestServiceRegister::getService(WebhookProxy::class),
                    TestServiceRegister::getService(WebhookConfigRepository::class)
                );
            }
        );

        TestServiceRegister::registerService(
            WebhookValidator::class,
            static function () {
                return new WebhookValidator(
                    TestServiceRegister::getService(WebhookConfigRepository::class)
                );
            }
        );

        TestServiceRegister::registerService(
            GuidProvider::CLASS_NAME,
            function () {
                return TestGuidProvider::getInstance();
            }
        );

        TestRepositoryRegistry::registerRepository(ConfigEntity::getClassName(), MemoryRepository::getClassName());
        TestRepositoryRegistry::registerRepository(
            QueueItem::getClassName(),
            QueueItemRepository::class
        );
        TestRepositoryRegistry::registerRepository(
            ConnectionSettings::getClassName(),
            MemoryRepositoryWithConditionalDelete::getClassName()
        );
        TestRepositoryRegistry::registerRepository(TransactionEntity::getClassName(), MemoryRepository::getClassName());
        TestRepositoryRegistry::registerRepository(
            TransactionLogEntity::getClassName(),
            MockShopLogRepository::getClassName()
        );
        TestRepositoryRegistry::registerRepository(WebhookConfig::getClassName(), MemoryRepository::getClassName());
        TestRepositoryRegistry::registerRepository(
            GeneralSettingsEntity::getClassName(),
            MemoryRepository::getClassName()
        );
        TestRepositoryRegistry::registerRepository(
            AdyenGivingSettingsEntity::getClassName(),
            MemoryRepository::getClassName()
        );
        TestRepositoryRegistry::registerRepository(
            OrderStatusMappingSettingsEntity::getClassName(),
            MemoryRepository::getClassName()
        );
        TestRepositoryRegistry::registerRepository(
            PaymentMethod::getClassName(),
            MockPaymentRepository::getClassName()
        );
        TestRepositoryRegistry::registerRepository(
            DonationsData::getClassName(),
            MockAdyenGivingRepository::getClassName()
        );
        TestRepositoryRegistry::registerRepository(
            DonationsData::getClassName(),
            MockAdyenGivingRepository::getClassName()
        );
        TestRepositoryRegistry::registerRepository(
            NotificationEntity::getClassName(),
            MockShopNotificationsRepository::getClassName()
        );
        TestRepositoryRegistry::registerRepository(
            DisconnectTime::getClassName(),
            MemoryRepository::getClassName()
        );
    }

    protected function tearDown(): void
    {
        TestRepositoryRegistry::cleanUp();
        MemoryStorage::reset();
        Logger::resetInstance();
        LoggerConfiguration::resetInstance();
    }
}
