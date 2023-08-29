<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\OrderMappings\Controller;

use Adyen\Core\BusinessLogic\AdminAPI\OrderMappings\Request\OrderMappingsRequest;
use Adyen\Core\BusinessLogic\AdminAPI\OrderMappings\Response\OrderMappingsGetResponse;
use Adyen\Core\BusinessLogic\AdminAPI\OrderMappings\Response\OrderMappingsPutResponse;
use Adyen\Core\BusinessLogic\Webhook\Services\OrderStatusMappingService;

class OrderMappingsController
{
    /**
     * @var OrderStatusMappingService
     */
    private $orderStatusMappingService;

    /**
     * @param OrderStatusMappingService $orderStatusMappingService
     */
    public function __construct(OrderStatusMappingService $orderStatusMappingService)
    {
        $this->orderStatusMappingService = $orderStatusMappingService;
    }

    /**
     * @param OrderMappingsRequest $orderMappingsRequest
     *
     * @return OrderMappingsPutResponse
     */
    public function saveOrderStatusMap(OrderMappingsRequest $orderMappingsRequest): OrderMappingsPutResponse
    {
        $this->orderStatusMappingService->saveOrderStatusMappingSettings(
            $orderMappingsRequest->getOrderStatusMap()
        );

        return new OrderMappingsPutResponse();
    }

    /**
     * @return OrderMappingsGetResponse
     */
    public function getOrderStatusMap(): OrderMappingsGetResponse
    {
        return new OrderMappingsGetResponse($this->orderStatusMappingService->getOrderStatusMappingSettings());
    }
}
