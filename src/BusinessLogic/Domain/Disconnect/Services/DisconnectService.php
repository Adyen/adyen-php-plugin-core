<?php

namespace Adyen\Core\BusinessLogic\Domain\Disconnect\Services;

use Adyen\Core\BusinessLogic\Domain\AdyenGivingSettings\Repositories\AdyenGivingSettingsRepository;
use Adyen\Core\BusinessLogic\Domain\Connection\Enums\Mode;
use Adyen\Core\BusinessLogic\Domain\Connection\Repositories\ConnectionSettingsRepository;
use Adyen\Core\BusinessLogic\Domain\Disconnect\Repositories\DisconnectRepository;
use Adyen\Core\BusinessLogic\Domain\Disconnect\Tasks\DisconnectTask;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Repositories\GeneralSettingsRepository;
use Adyen\Core\BusinessLogic\Domain\Integration\Payment\ShopPaymentService;
use Adyen\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Adyen\Core\BusinessLogic\Domain\Payment\Repositories\PaymentMethodConfigRepository;
use Adyen\Core\BusinessLogic\Domain\Webhook\Proxies\WebhookProxy;
use Adyen\Core\BusinessLogic\Domain\Webhook\Repositories\WebhookConfigRepository;
use Adyen\Core\BusinessLogic\Webhook\Repositories\OrderStatusMappingRepository;
use Adyen\Core\Infrastructure\Logger\Logger;
use Adyen\Core\Infrastructure\ServiceRegister;
use Adyen\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;
use Adyen\Core\Infrastructure\TaskExecution\QueueService;
use DateTime;
use Exception;

/**
 * Class DisconnectService
 *
 * @package Adyen\Core\BusinessLogic\Domain\Disconnect\Services
 */
class DisconnectService
{
    /**
     * @var WebhookConfigRepository
     */
    protected $webhookRepository;
    /**
     * @var ConnectionSettingsRepository
     */
    protected $connectionSettingsRepository;
    /**
     * @var ShopPaymentService
     */
    protected $shopPaymentService;
    /**
     * @var QueueService
     */
    protected $queueService;
    /**
     * @var AdyenGivingSettingsRepository
     */
    protected $givingRepository;
    /**
     * @var GeneralSettingsRepository
     */
    protected $generalSettingsRepository;
    /**
     * @var OrderStatusMappingRepository
     */
    protected $orderMappingRepository;
    /**
     * @var PaymentMethodConfigRepository
     */
    protected $paymentConfigRepository;
    /**
     * @var DisconnectRepository
     */
    protected $disconnectRepository;

    /**
     * @param WebhookConfigRepository $webhookRepository
     * @param ConnectionSettingsRepository $connectionSettingsRepository
     * @param ShopPaymentService $shopPaymentService
     * @param QueueService $queueService
     * @param AdyenGivingSettingsRepository $givingRepository
     * @param GeneralSettingsRepository $generalSettingsRepository
     * @param OrderStatusMappingRepository $orderMappingRepository
     * @param PaymentMethodConfigRepository $paymentConfigRepository
     * @param DisconnectRepository $disconnectRepository
     */
    public function __construct(
        WebhookConfigRepository       $webhookRepository,
        ConnectionSettingsRepository  $connectionSettingsRepository,
        ShopPaymentService            $shopPaymentService,
        QueueService                  $queueService,
        AdyenGivingSettingsRepository $givingRepository,
        GeneralSettingsRepository     $generalSettingsRepository,
        OrderStatusMappingRepository  $orderMappingRepository,
        PaymentMethodConfigRepository $paymentConfigRepository,
        DisconnectRepository          $disconnectRepository
    )
    {
        $this->webhookRepository = $webhookRepository;
        $this->connectionSettingsRepository = $connectionSettingsRepository;
        $this->shopPaymentService = $shopPaymentService;
        $this->queueService = $queueService;
        $this->givingRepository = $givingRepository;
        $this->generalSettingsRepository = $generalSettingsRepository;
        $this->orderMappingRepository = $orderMappingRepository;
        $this->paymentConfigRepository = $paymentConfigRepository;
        $this->disconnectRepository = $disconnectRepository;
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function disconnect(): void
    {
        try {
            $this->removeWebhook();
            $this->disconnectIntegration();
            $this->deleteAllData();
        } catch (Exception $e) {
            Logger::logWarning($e->getMessage());

            throw $e;
        }
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function removeWebhook(): void
    {
        $connectionSettings = $this->connectionSettingsRepository->getConnectionSettings();

        if (!$connectionSettings ||
            ($connectionSettings->getMode() === Mode::MODE_LIVE &&
                !$connectionSettings->getLiveData()->getClientPrefix())
        ) {
            return;
        }

        $webhookConfig = $this->webhookRepository->getWebhookConfig();

        if (!$webhookConfig) {
            return;
        }

        /** @var WebhookProxy $proxy */
        $proxy = ServiceRegister::getService(WebhookProxy::class);
        try {
            $proxy->deleteWebhook($connectionSettings->getActiveConnectionData()->getMerchantId(),
                $webhookConfig->getId());
        } catch (Exception $e) {
            Logger::logWarning($e->getMessage());
        }

        $this->webhookRepository->deleteWebhookConfig();
    }

    /**
     * @return void
     *
     * @throws QueueStorageUnavailableException
     * @throws Exception
     */
    public function disconnectIntegration(): void
    {
        $this->shopPaymentService->deleteAllPaymentMethods();
        $this->connectionSettingsRepository->deleteConnectionSettings();
        $this->givingRepository->deleteAdyenGivingSettings();
        $this->generalSettingsRepository->deleteGeneralSettings();
        $this->orderMappingRepository->deleteOrderStatusMapping();
        $this->paymentConfigRepository->deleteConfiguredMethods();
    }

    /**
     * @return void
     *
     * @throws QueueStorageUnavailableException
     * @throws Exception
     */
    protected function deleteAllData(): void
    {
        $disconnectTime = new DateTime();
        $this->disconnectRepository->setDisconnectTime($disconnectTime);
        $this->queueService->enqueue(
            'disconnect-integration',
            new DisconnectTask(StoreContext::getInstance()->getStoreId(), $disconnectTime)
        );
    }
}
