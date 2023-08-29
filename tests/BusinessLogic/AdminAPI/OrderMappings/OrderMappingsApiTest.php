<?php

namespace Adyen\Core\Tests\BusinessLogic\AdminAPI\OrderMappings;

use Adyen\Core\BusinessLogic\AdminAPI\AdminAPI;
use Adyen\Core\BusinessLogic\AdminAPI\OrderMappings\Controller\OrderMappingsController;
use Adyen\Core\BusinessLogic\AdminAPI\OrderMappings\Request\OrderMappingsRequest;
use Adyen\Core\BusinessLogic\AdminAPI\OrderMappings\Response\OrderMappingsGetResponse;
use Adyen\Core\BusinessLogic\Bootstrap\SingleInstance;
use Adyen\Core\BusinessLogic\Domain\OrderSettings\Models\OrderStatusMapping;
use Adyen\Core\BusinessLogic\Webhook\Repositories\OrderStatusMappingRepository;
use Adyen\Core\BusinessLogic\Webhook\Services\OrderStatusMappingService;
use Adyen\Core\Tests\BusinessLogic\AdminAPI\OrderMappings\MockComponents\MockOrderMappingRepository;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;
use Adyen\Webhook\PaymentStates;
use Exception;

/**
 * Class OrderMappingsApiTest
 *
 * @package Adyen\Core\Tests\BusinessLogic\AdminAPI\OrderMappings
 */
class OrderMappingsApiTest extends BaseTestCase
{
    /**
     * @var MockOrderMappingRepository
     */
    private $orderStatusMappingRepository;

    /**
     * @var OrderStatusMappingService
     */
    private $service;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->orderStatusMappingRepository = new MockOrderMappingRepository();
        $this->service = TestServiceRegister::getService(OrderStatusMappingService::class);

        TestServiceRegister::registerService(
            OrderStatusMappingRepository::class,
            new SingleInstance(function () {
                return $this->orderStatusMappingRepository;
            })
        );

        TestServiceRegister::registerService(
            OrderMappingsController::class,
            new SingleInstance(function () {
                return new OrderMappingsController(TestServiceRegister::getService(OrderStatusMappingService::class));
            })
        );
    }

    /**
     * @return void
     */
    public function testIsGetResponseSuccessful(): void
    {
        // Arrange
        $this->orderStatusMappingRepository->setOrderStatusMapping(
            ['1', '2', '3', '4', '5', '6', '7', '8', '9']

        );

        // Act
        $response = AdminAPI::get()->orderMappings('1')->getOrderStatusMap();

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @return void
     */
    public function testGetResponse(): void
    {
        // Arrange
        $map = [
            '1',
            '2',
            '3',
            '4',
            '5',
            '6',
            '7',
            '8',
            '9'
        ];
        $this->orderStatusMappingRepository->setOrderStatusMapping(
            $map
        );
        $expectedResponse = new OrderMappingsGetResponse($map);
        // Act
        $response = AdminAPI::get()->orderMappings('1')->getOrderStatusMap();

        // Assert
        self::assertEquals($expectedResponse, $response);
    }

    /**
     * @return void
     */
    public function testGetResponseToArray(): void
    {
        // Arrange
        $map = [
            PaymentStates::STATE_IN_PROGRESS => '1',
            PaymentStates::STATE_PENDING => '2',
            PaymentStates::STATE_PAID => '3',
            PaymentStates::STATE_FAILED => '4',
            PaymentStates::STATE_REFUNDED => '5',
            PaymentStates::STATE_CANCELLED => '6',
            PaymentStates::STATE_PARTIALLY_REFUNDED => '7',
            PaymentStates::STATE_NEW => '8',
            PaymentStates::CHARGE_BACK => '9'
        ];
        $this->orderStatusMappingRepository->setOrderStatusMapping(
            $map
        );
        $expectedResponse = new OrderMappingsGetResponse($map);
        // Act
        $response = AdminAPI::get()->orderMappings('1')->getOrderStatusMap();

        // Assert
        self::assertEquals($expectedResponse->toArray(), $response->toArray());
    }

    /**
     * @return void
     */
    public function testIsPutResponseSuccessful(): void
    {
        // Arrange
        $orderMappingRequest = OrderMappingsRequest::parse([
            '1',
            '2',
            '3',
            '4',
            '5',
            '6',
            '7',
            '8',
            '9'
        ]);

        // Act
        $response = AdminAPI::get()->orderMappings('1')->saveOrderStatusMap($orderMappingRequest);

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @return void
     */
    public function testPutResponseToArray(): void
    {
        // Arrange
        $orderMappingRequest = OrderMappingsRequest::parse([
            '1',
            '2',
            '3',
            '4',
            '5',
            '6',
            '7',
            '8',
            '9'
        ]);

        // Act
        $response = AdminAPI::get()->orderMappings('1')->saveOrderStatusMap($orderMappingRequest);

        // Assert
        self::assertEquals(['success' => true], $response->toArray());
    }
}
