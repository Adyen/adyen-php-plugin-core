<?php

namespace Adyen\Core\Tests\BusinessLogic\AdminAPI\WebhookNotifications;

use Adyen\Core\BusinessLogic\AdminAPI\AdminAPI;
use Adyen\Core\BusinessLogic\Bootstrap\SingleInstance;
use Adyen\Core\BusinessLogic\DataAccess\TransactionLog\Entities\TransactionLog;
use Adyen\Core\BusinessLogic\Domain\Disconnect\Repositories\DisconnectRepository;
use Adyen\Core\BusinessLogic\Domain\Integration\Order\OrderService;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Services\TransactionHistoryService;
use Adyen\Core\BusinessLogic\TransactionLog\Repositories\TransactionLogRepository as TransactionLogRepositoryInterface;
use Adyen\Core\BusinessLogic\TransactionLog\Services\TransactionLogService;
use Adyen\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use Adyen\Core\Infrastructure\TaskExecution\QueueItem;
use Adyen\Core\Infrastructure\Utility\TimeProvider;
use Adyen\Core\Tests\BusinessLogic\AdminAPI\WebhookNotifications\MockComponents\MockTransactionLogService;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;
use DateTime;
use DateTimeInterface;
use Exception;

/**
 * Class WebhookNotificationsControllerTest
 *
 * @package Adyen\Core\Tests\BusinessLogic\AdminAPI\WebhookNotifications
 */
class WebhookNotificationsControllerTest extends BaseTestCase
{
    /**
     * @var TransactionLogService
     */
    private $service;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->service = new MockTransactionLogService(
            TestServiceRegister::getService(TransactionHistoryService::class),
            TestServiceRegister::getService(TransactionLogRepositoryInterface::class),
            TestServiceRegister::getService(OrderService::class),
            TestServiceRegister::getService(DisconnectRepository::class)
        );

        TestServiceRegister::registerService(
            TransactionLogService::class,
            new SingleInstance(function () {
                return $this->service;
            })
        );
    }

    /**
     * @return void
     *
     * @throws QueryFilterInvalidParamException
     */
    public function testIsGetResponseSuccessful(): void
    {
        // Arrange

        // Act
        $response = AdminAPI::get()->webhookNotifications('store1')->getNotifications(1, 10);

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @return void
     * @throws QueryFilterInvalidParamException
     */
    public function testResponse(): void
    {
        // Arrange

        $this->service->setMockLogs($this->generateLogs());
        // Act
        $response = AdminAPI::get()->webhookNotifications('store1')->getNotifications(1, 10);

        // Assert
        self::assertEquals($this->expectedToArray(), $response->toArray());
    }

    private function expectedToArray(): array
    {
        return [
            'nextPageAvailable' => false,
            'notifications' => [
                [
                    'orderId' => 'merch1',
                    'paymentMethod' => 'method',
                    'notificationId' => 1,
                    'dateAndTime' => TimeProvider::getInstance()
                        ->getDateTime(1609459200)
                        ->format(DateTimeInterface::ATOM),
                    'code' => 'code',
                    'successful' => true,
                    'status' => QueueItem::QUEUED,
                    'hasDetails' => true,
                    'details' => [
                        'reason' => 'reason1',
                        'failureDescription' => '',
                        'adyenLink' => 'adyenLink',
                        'shopLink' => 'link1'
                    ],
                    'logo' => 'https://checkoutshopper-live.adyen.com/checkoutshopper/images/logos/method.svg'
                ],
                [
                    'orderId' => 'merch2',
                    'paymentMethod' => 'method2',
                    'notificationId' => 2,
                    'dateAndTime' => TimeProvider::getInstance()
                        ->getDateTime(1609459200)
                        ->format(DateTimeInterface::ATOM),
                    'code' => 'code2',
                    'successful' => false,
                    'status' => QueueItem::FAILED,
                    'hasDetails' => false,
                    'details' => [
                        'reason' => '',
                        'failureDescription' => '',
                        'adyenLink' => null,
                        'shopLink' => null
                    ],
                    'logo' => 'https://checkoutshopper-live.adyen.com/checkoutshopper/images/logos/method2.svg'
                ]
            ],
        ];
    }

    /**
     * @return TransactionLog[]
     */
    private function generateLogs(): array
    {
        $log1 = new TransactionLog();
        $log1->setQueueStatus(QueueItem::QUEUED);
        $log1->setAdyenLink('adyenLink');
        $log1->setId(1);
        $log1->setReason('reason1');
        $log1->setMerchantReference('merch1');
        $log1->setPaymentMethod('method');
        $log1->setEventCode('code');
        $log1->setTimestamp(
            (DateTime::createFromFormat(DateTimeInterface::ATOM, '2021-01-01T01:00:00+01:00'))->getTimestamp()
        );
        $log1->setStoreId('1');
        $log1->setIsSuccessful(true);
        $log1->setExecutionId(1);
        $log1->setShopLink('link1');

        $log2 = new TransactionLog();
        $log2->setQueueStatus(QueueItem::FAILED);
        $log2->setId(2);
        $log2->setMerchantReference('merch2');
        $log2->setPaymentMethod('method2');
        $log2->setEventCode('code2');
        $log2->setTimestamp(
            (DateTime::createFromFormat(DateTimeInterface::ATOM, '2021-01-01T01:00:00+01:00'))->getTimestamp()
        );
        $log2->setStoreId('1');
        $log2->setIsSuccessful(false);
        $log2->setExecutionId(2);

        return [
            $log1,
            $log2
        ];
    }
}
