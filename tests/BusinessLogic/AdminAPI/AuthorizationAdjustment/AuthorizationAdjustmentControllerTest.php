<?php

namespace Adyen\Core\Tests\BusinessLogic\AdminAPI\AuthorizationAdjustment;

use Adyen\Core\BusinessLogic\AdminAPI\AdminAPI;
use Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Exceptions\InvalidAuthorizationTypeException;
use Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Exceptions\InvalidPaymentStateException;
use Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Exceptions\OrderFullyCapturedException;
use Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Exceptions\PaymentLinkExistsException;
use Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Handlers\AuthorizationAdjustmentHandler;
use Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Proxies\AuthorizationAdjustmentProxy;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\CurrencyMismatchException;
use Adyen\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use Adyen\Core\BusinessLogic\Domain\ShopNotifications\Services\ShopNotificationService;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Exceptions\InvalidMerchantReferenceException;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Services\TransactionHistoryService;
use Adyen\Core\Tests\BusinessLogic\AdminAPI\AuthorizationAdjustment\Mocks\MockAuthorizationAdjustmentHandler;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Adyen\Core\Tests\BusinessLogic\Domain\AuthorizationAdjustment\Mocks\MockAdjustmentProxy;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;
use Exception;

/**
 * Class AuthorizationAdjustmentControllerTest.
 *
 * @package Adyen\Core\Tests\BusinessLogic\AdminAPI\AuthorizationAdjustment
 */
class AuthorizationAdjustmentControllerTest extends BaseTestCase
{
    /**
     * @var MockAdjustmentProxy
     */
    private $proxy;

    /**
     * @var MockAuthorizationAdjustmentHandler
     */
    private $handler;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->proxy = new MockAdjustmentProxy();
        TestServiceRegister::registerService(
            AuthorizationAdjustmentProxy::class,
            function () {
                return $this->proxy;
            }
        );

        $this->handler = new MockAuthorizationAdjustmentHandler(
            TestServiceRegister::getService(TransactionHistoryService::class),
            TestServiceRegister::getService(ShopNotificationService::class),
            TestServiceRegister::getService(AuthorizationAdjustmentProxy::class),
            TestServiceRegister::getService(ConnectionService::class)
        );
        TestServiceRegister::registerService(
            AuthorizationAdjustmentHandler::class,
            function () {
                return $this->handler;
            }
        );
    }

    /**
     * @return void
     *
     * @throws InvalidAuthorizationTypeException
     * @throws InvalidPaymentStateException
     * @throws OrderFullyCapturedException
     * @throws PaymentLinkExistsException
     * @throws CurrencyMismatchException
     * @throws InvalidMerchantReferenceException
     */
    public function testSuccess(): void
    {
        // Arrange
        // Act
        $response = AdminAPI::get()->authorizationAdjustment('storeId')->handleExtendingAuthorizationPeriod('merchantReference');

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @return void
     *
     * @throws InvalidAuthorizationTypeException
     * @throws InvalidPaymentStateException
     * @throws OrderFullyCapturedException
     * @throws PaymentLinkExistsException
     * @throws CurrencyMismatchException
     * @throws InvalidMerchantReferenceException
     */
    public function testSuccessToArray(): void
    {
        // Arrange
        // Act
        $response = AdminAPI::get()->authorizationAdjustment('storeId')->handleExtendingAuthorizationPeriod('merchantReference');

        // Assert
        self::assertEquals(['success' => true], $response->toArray());
    }

    /**
     * @return void
     *
     * @throws InvalidAuthorizationTypeException
     * @throws InvalidPaymentStateException
     * @throws OrderFullyCapturedException
     * @throws PaymentLinkExistsException
     * @throws CurrencyMismatchException
     * @throws InvalidMerchantReferenceException
     */
    public function testFail(): void
    {
        // Arrange
        $this->handler->setSuccess(false);
        // Act
        $response = AdminAPI::get()->authorizationAdjustment('storeId')->handleExtendingAuthorizationPeriod('merchantReference');

        // Assert
        self::assertFalse($response->isSuccessful());
    }

    /**
     * @return void
     *
     * @throws InvalidAuthorizationTypeException
     * @throws InvalidPaymentStateException
     * @throws OrderFullyCapturedException
     * @throws PaymentLinkExistsException
     * @throws CurrencyMismatchException
     * @throws InvalidMerchantReferenceException
     */
    public function testFailToArray(): void
    {
        // Arrange
        $this->handler->setSuccess(false);
        // Act
        $response = AdminAPI::get()->authorizationAdjustment('storeId')->handleExtendingAuthorizationPeriod('merchantReference');

        // Assert
        self::assertEquals(['success' => false], $response->toArray());
    }

    /**
     * @return void
     *
     * @throws CurrencyMismatchException
     * @throws InvalidAuthorizationTypeException
     * @throws InvalidMerchantReferenceException
     * @throws InvalidPaymentStateException
     * @throws OrderFullyCapturedException
     * @throws PaymentLinkExistsException
     */
    public function testFailWhenExceptionIsThrown(): void
    {
        // Arrange
        $this->handler->setException(new Exception('Exception test'));
        // Act
        $response = AdminAPI::get()->authorizationAdjustment('storeId')->handleExtendingAuthorizationPeriod('merchantReference');

        // Assert
        self::assertFalse($response->isSuccessful());
    }
}
