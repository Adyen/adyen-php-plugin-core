<?php

namespace Adyen\Core\Tests\BusinessLogic\Domain\Checkout\PaymentRequest\Services;

use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\InvalidPaymentMethodCodeException;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Services\PaymentCheckoutConfigService;
use Adyen\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use Adyen\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Adyen\Core\BusinessLogic\Domain\Payment\Exceptions\PaymentMethodDataEmptyException;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\PaymentMethod;
use Adyen\Core\Tests\BusinessLogic\AdminAPI\Store\MockComponents\MockConnectionSettingsRepository;
use Adyen\Core\Tests\BusinessLogic\CheckoutAPI\CheckoutConfig\MockComponents\MockPaymentsProxy;
use Adyen\Core\Tests\BusinessLogic\CheckoutAPI\CheckoutConfig\MockComponents\MockStoredDetailsProxy;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Adyen\Core\Tests\BusinessLogic\Common\MockComponents\MockPaymentMethodConfigRepository;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;
use Exception;

/**
 * Class PaymentCheckoutConfigServiceTest
 *
 * @package Adyen\Core\Tests\BusinessLogic\Domain\Checkout\PaymentRequest\Services
 */
class PaymentCheckoutConfigServiceTest extends BaseTestCase
{
    /**
     * @var MockPaymentMethodConfigRepository
     */
    private $paymentMethodConfigRepo;

    /**
     * @var PaymentCheckoutConfigService
     */
    private $service;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->paymentMethodConfigRepo = new MockPaymentMethodConfigRepository();

        $this->service = new PaymentCheckoutConfigService(
            new MockConnectionSettingsRepository(),
            $this->paymentMethodConfigRepo,
            new MockPaymentsProxy(),
            new MockStoredDetailsProxy(),
            TestServiceRegister::getService(ConnectionService::class)
        );
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testHasEnabledExpressCheckoutPaymentMethodsReturnsFalseWhenNoneEnabled(): void
    {
        // Arrange
        $this->paymentMethodConfigRepo->setEnabledExpressCheckoutPaymentMethods([]);

        // Act
        $result = StoreContext::doWithStore(
            'store1',
            [$this->service, 'hasEnabledExpressCheckoutPaymentMethods']
        );

        // Assert
        self::assertFalse($result);
    }

    /**
     * @return void
     *
     * @throws InvalidPaymentMethodCodeException
     * @throws PaymentMethodDataEmptyException
     */
    public function testHasEnabledExpressCheckoutPaymentMethodsReturnsTrueWhenAnyEnabled(): void
    {
        // Arrange
        $this->paymentMethodConfigRepo->setEnabledExpressCheckoutPaymentMethods(
            [new PaymentMethod('test', 'scheme', 'test', 'http://test.example.com', true, [], [], 'cards')]
        );

        // Act
        $result = StoreContext::doWithStore(
            'store1',
            [$this->service, 'hasEnabledExpressCheckoutPaymentMethods']
        );

        // Assert
        self::assertTrue($result);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testHasEnabledExpressCheckoutPaymentMethodsForwardsGuestFlag(): void
    {
        // Act
        StoreContext::doWithStore(
            'store1',
            [$this->service, 'hasEnabledExpressCheckoutPaymentMethods'],
            [true]
        );

        // Assert
        self::assertTrue($this->paymentMethodConfigRepo->getIsGuestArgument());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testHasEnabledExpressCheckoutPaymentMethodsDefaultsGuestFlagToFalse(): void
    {
        // Act
        StoreContext::doWithStore(
            'store1',
            [$this->service, 'hasEnabledExpressCheckoutPaymentMethods']
        );

        // Assert
        self::assertFalse($this->paymentMethodConfigRepo->getIsGuestArgument());
    }
}
