<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\InfoSettings\Controller;

use Adyen\Core\BusinessLogic\AdminAPI\AdminAPI;
use Adyen\Core\BusinessLogic\AdminAPI\InfoSettings\Response\SystemInfoResponse;
use Adyen\Core\BusinessLogic\DataAccess\Connection\Entities\ConnectionSettings;
use Adyen\Core\BusinessLogic\Domain\Integration\SystemInfo\SystemInfoService;
use Adyen\Core\BusinessLogic\Domain\Payment\Repositories\PaymentMethodConfigRepository;
use Adyen\Core\BusinessLogic\Domain\Stores\Services\StoreService;
use Adyen\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use Adyen\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use Adyen\Core\Infrastructure\ORM\QueryFilter\Operators;
use Adyen\Core\Infrastructure\ORM\QueryFilter\QueryFilter;
use Adyen\Core\Infrastructure\TaskExecution\QueueItem;
use Exception;

/**
 * Class SystemInfoController
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\InfoSettings\Controller
 */
class SystemInfoController
{
    /**
     * @var SystemInfoService
     */
    private $systemInfoService;

    /**
     * @var PaymentMethodConfigRepository
     */
    private $paymentMethodRepository;

    /**
     * @var RepositoryInterface
     */
    private $queueItemRepository;

    /**
     * @var RepositoryInterface
     */
    private $connectionSettingsRepository;

    /**
     * @var StoreService
     */
    private $storeService;

    /**
     * @param SystemInfoService $systemInfoService
     * @param PaymentMethodConfigRepository $paymentMethodRepository
     * @param RepositoryInterface $queueItemRepository
     * @param RepositoryInterface $connectionSettingsRepository
     * @param StoreService $storeService
     */
    public function __construct(
        SystemInfoService $systemInfoService,
        PaymentMethodConfigRepository $paymentMethodRepository,
        RepositoryInterface $queueItemRepository,
        RepositoryInterface $connectionSettingsRepository,
        StoreService $storeService
    ) {
        $this->systemInfoService = $systemInfoService;
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->queueItemRepository = $queueItemRepository;
        $this->connectionSettingsRepository = $connectionSettingsRepository;
        $this->storeService = $storeService;
    }

    /**
     * @return SystemInfoResponse
     *
     * @throws QueryFilterInvalidParamException
     * @throws Exception
     */
    public function getSystemInfo(): SystemInfoResponse
    {
        return new SystemInfoResponse(
            $this->phpInfo(),
            $this->systemInfoService->getSystemInfo(),
            $this->paymentMethodRepository->getConfiguredPaymentMethodsEntities(),
            $this->notCompletedQueueItems(),
            $this->connectionSettings(),
            $this->webhookValidation()
        );
    }

    /**
     * @return string
     *
     * @throws Exception
     */
    private function webhookValidation(): string
    {
        $storeId = $this->storeService->getFirstConnectedStoreId();

        return json_encode(AdminAPI::get()->webhookValidation($storeId)->report()->toArray());
    }

    /**
     * @return QueueItem[]
     *
     * @throws QueryFilterInvalidParamException
     */
    private function notCompletedQueueItems(): array
    {
        $query = new QueryFilter();
        $query->where('status', Operators::NOT_EQUALS, QueueItem::COMPLETED);

        return $this->queueItemRepository->select($query);
    }

    /**
     * @return ConnectionSettings[]
     */
    private function connectionSettings(): array
    {
        $query = new QueryFilter();

        return $this->connectionSettingsRepository->select($query);
    }

    /**
     * @return false|string
     */
    private function phpInfo(): string
    {
        ob_start();
        phpinfo();

        return ob_get_clean();
    }
}
