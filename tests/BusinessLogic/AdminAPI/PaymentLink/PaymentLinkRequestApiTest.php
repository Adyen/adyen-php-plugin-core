<?php

namespace Adyen\Core\Tests\BusinessLogic\AdminAPI\PaymentLink;

use Adyen\Core\BusinessLogic\AdminAPI\AdminAPI;
use Adyen\Core\BusinessLogic\AdminAPI\PaymentLink\Request\CreatePaymentLinkRequest;
use Adyen\Core\BusinessLogic\Bootstrap\SingleInstance;
use Adyen\Core\BusinessLogic\DataAccess\Connection\Repositories\ConnectionSettingsRepository;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentLink\Proxies\PaymentLinkProxy;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\InvalidPaymentMethodCodeException;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Currency;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Proxies\PaymentsProxy;
use Adyen\Core\BusinessLogic\Domain\Checkout\Processors\AmountProcessor;
use Adyen\Core\BusinessLogic\Domain\Checkout\Processors\MerchantIdProcessor;
use Adyen\Core\BusinessLogic\Domain\Checkout\Processors\PaymentLinkRequest\AllowedPaymentMethodsProcessor;
use Adyen\Core\BusinessLogic\Domain\Checkout\Processors\PaymentLinkRequest\ExpiresAtProcessor;
use Adyen\Core\BusinessLogic\Domain\Checkout\Processors\PaymentLinkRequest\PaymentLinkRequestProcessorsRegistry;
use Adyen\Core\BusinessLogic\Domain\Checkout\Processors\ReferenceProcessor;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Repositories\GeneralSettingsRepository;
use Adyen\Core\BusinessLogic\Domain\GeneralSettings\Services\GeneralSettingsService;
use Adyen\Core\BusinessLogic\Domain\Integration\Order\OrderService;
use Adyen\Core\BusinessLogic\Domain\Payment\Exceptions\PaymentMethodDataEmptyException;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\MethodAdditionalData\AmazonPay;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\MethodAdditionalData\Oney;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\PaymentMethod;
use Adyen\Core\BusinessLogic\Domain\Payment\Proxies\PaymentProxy;
use Adyen\Core\BusinessLogic\Domain\Payment\Repositories\PaymentMethodConfigRepository;
use Adyen\Core\BusinessLogic\Domain\Payment\Services\PaymentService;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Exceptions\InvalidMerchantReferenceException;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Services\TransactionHistoryService;
use Adyen\Core\Tests\BusinessLogic\AdminAPI\PaymentLink\MockComponents\MockGeneralSettingsService;
use Adyen\Core\Tests\BusinessLogic\AdminAPI\PaymentLink\MockComponents\MockPaymentLinkProxy;
use Adyen\Core\Tests\BusinessLogic\AdminAPI\PaymentLink\MockComponents\MockPaymentService;
use Adyen\Core\Tests\BusinessLogic\AdminAPI\Store\MockComponents\MockConnectionSettingsRepository;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;
use DateTime;

/**
 * Class PaymentLinkRequestApiTest
 *
 * @package Adyen\Core\Tests\BusinessLogic\AdminAPI\PaymentLink
 */
class PaymentLinkRequestApiTest extends BaseTestCase
{
    /**
     * @var MockPaymentLinkProxy
     */
    private $proxy;
    /**
     * @var MockPaymentService
     */
    private $paymentService;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->proxy = new MockPaymentLinkProxy();

        TestServiceRegister::registerService(
            ConnectionSettingsRepository::class,
            new SingleInstance(function () {
                return new MockConnectionSettingsRepository();
            })
        );

        $this->paymentService = new MockPaymentService(
            TestServiceRegister::getService(PaymentMethodConfigRepository::class),
            TestServiceRegister::getService(ConnectionSettingsRepository::class),
            TestServiceRegister::getService(PaymentProxy::class),
            TestServiceRegister::getService(PaymentsProxy::class)
        );

        TestServiceRegister::registerService(
            PaymentService::class,
            new SingleInstance(function () {
                return $this->paymentService;
            })
        );

        TestServiceRegister::registerService(
            GeneralSettingsService::class,
            new SingleInstance(function () {
                return new MockGeneralSettingsService(
                    TestServiceRegister::getService(GeneralSettingsRepository::class)
                );
            })
        );

        TestServiceRegister::registerService(
            PaymentLinkProxy::class,
            function () {
                return $this->proxy;
            }
        );
        TestServiceRegister::registerService(
            AmountProcessor::class,
            new SingleInstance(static function () {
                return new AmountProcessor(
                    TestServiceRegister::getService(TransactionHistoryService::class),
                    TestServiceRegister::getService(OrderService::class)
                );
            })
        );

        TestServiceRegister::registerService(
            ReferenceProcessor::class,
            new SingleInstance(static function () {
                return new ReferenceProcessor();
            })
        );

        TestServiceRegister::registerService(
            MerchantIdProcessor::class,
            new SingleInstance(static function () {
                return new MerchantIdProcessor(TestServiceRegister::getService(ConnectionSettingsRepository::class));
            })
        );

        TestServiceRegister::registerService(
            AllowedPaymentMethodsProcessor::class,
            new SingleInstance(static function () {
                return new AllowedPaymentMethodsProcessor(TestServiceRegister::getService(PaymentService::class));
            })
        );

        TestServiceRegister::registerService(
            ExpiresAtProcessor::class,
            new SingleInstance(static function () {
                return new ExpiresAtProcessor(TestServiceRegister::getService(GeneralSettingsService::class));
            })
        );

        PaymentLinkRequestProcessorsRegistry::registerGlobal(AmountProcessor::class);
        PaymentLinkRequestProcessorsRegistry::registerGlobal(ReferenceProcessor::class);
        PaymentLinkRequestProcessorsRegistry::registerGlobal(MerchantIdProcessor::class);
        PaymentLinkRequestProcessorsRegistry::registerGlobal(AllowedPaymentMethodsProcessor::class);
        PaymentLinkRequestProcessorsRegistry::registerGlobal(ExpiresAtProcessor::class);
    }

    /**
     * @return void
     *
     * @throws InvalidMerchantReferenceException
     * @throws InvalidPaymentMethodCodeException
     * @throws PaymentMethodDataEmptyException
     */
    public function testSuccess(): void
    {
        // Arrange
        $context = new CreatePaymentLinkRequest(456.12, 'EUR', '1', new DateTime('2020-12-18'));
        $this->paymentService->setPaymentMethods($this->getPaymentMethods());

        // Act
        $response = AdminAPI::get()->paymentLink('storeId')->createPaymentLink($context);

        // Assert
        self::assertTrue($response->isSuccessful());
        self::assertTrue($this->proxy->getIsCalled());
        self::assertEquals(
            Amount::fromFloat(456.12, Currency::getDefault()),
            $this->proxy->getLastRequest()->getAmount()
        );
        self::assertEquals('1', $this->proxy->getLastRequest()->getReference());
        self::assertEquals('1', $this->proxy->getLastRequest()->getMerchantAccount());
        self::assertCount(3, $this->proxy->getLastRequest()->getAllowedPaymentMethods());
        self::assertEquals(['applepay', 'ideal', 'paypal'], $this->proxy->getLastRequest()->getAllowedPaymentMethods());
        self::assertEquals('2020-12-18T00:00:00+00:00', $this->proxy->getLastRequest()->getExpiresAt());
    }

    /**
     * @return void
     *
     * @throws InvalidMerchantReferenceException
     * @throws InvalidPaymentMethodCodeException
     * @throws PaymentMethodDataEmptyException
     */
    public function testNoPaymentMethods(): void
    {
        // Arrange
        $amount = Amount::fromFloat(456.12, Currency::getDefault());
        $context = new CreatePaymentLinkRequest(456.12, 'EUR', '1', new DateTime('2020-12-18'));

        // Act
        $response = AdminAPI::get()->paymentLink('storeId')->createPaymentLink($context);

        // Assert
        self::assertFalse($response->isSuccessful());
        self::assertEquals(
            [
                'errorCode' => 'paymentLink.noMethods',
                'errorMessage' => 'There are no payment methods that supports payment link request.',
                'errorParameters' => []
            ],
            $response->toArray()
        );

        $this->paymentService->setPaymentMethods($this->getUnsupportedMethod());
        $response = AdminAPI::get()->paymentLink('storeId')->createPaymentLink($context);
        self::assertFalse($response->isSuccessful());
    }

    /**
     * @return void
     *
     * @throws PaymentMethodDataEmptyException
     * @throws InvalidMerchantReferenceException
     * @throws InvalidPaymentMethodCodeException
     */
    public function testOneyPaymentMethods(): void
    {
        // Arrange
        $amount = Amount::fromFloat(456.12, Currency::getDefault());
        $context = new CreatePaymentLinkRequest(456.12, 'EUR', '1', new DateTime('2020-12-18'));
        $this->paymentService->setPaymentMethods(array_merge($this->getPaymentMethods(), $this->getOneyMethod()));

        // Act
        $response = AdminAPI::get()->paymentLink('storeId')->createPaymentLink($context);

        // Assert
        self::assertTrue($response->isSuccessful());
        self::assertCount(6, $this->proxy->getLastRequest()->getAllowedPaymentMethods());
        self::assertEquals(['applepay', 'ideal', 'paypal', 'facilypay_3x', 'facilypay_4x', 'facilypay_6x'],
            $this->proxy->getLastRequest()->getAllowedPaymentMethods());
    }

    /**
     * @return void
     *
     * @throws PaymentMethodDataEmptyException
     * @throws InvalidMerchantReferenceException
     * @throws InvalidPaymentMethodCodeException
     */
    public function testUnsupportedPaymentMethods(): void
    {
        // Arrange
        $amount = Amount::fromFloat(456.12, Currency::getDefault());
        $context = new CreatePaymentLinkRequest(456.12, 'EUR', '1', new DateTime('2020-12-18'));
        $this->paymentService->setPaymentMethods(
            array_merge($this->getPaymentMethods(), $this->getUnsupportedMethod())
        );

        // Act
        $response = AdminAPI::get()->paymentLink('storeId')->createPaymentLink($context);

        // Assert
        self::assertTrue($response->isSuccessful());
        self::assertCount(3, $this->proxy->getLastRequest()->getAllowedPaymentMethods());
        self::assertEquals(['applepay', 'ideal', 'paypal'], $this->proxy->getLastRequest()->getAllowedPaymentMethods());
    }

    /**
     * @return PaymentMethod[]
     *
     * @throws PaymentMethodDataEmptyException
     * @throws InvalidPaymentMethodCodeException
     */
    private function getPaymentMethods(): array
    {
        return [
            new PaymentMethod(
                'PM3224P6322322225FNZ7B8G595',
                'scheme',
                'Credit Card',
                'https://checkoutshopper-live.adyen.com/checkoutshopper/images/logos/card.svg',
                true,
                ['USD'],
                ['ANY'],
                'creditOrDebitCard'
            ),
            new PaymentMethod(
                'PM3224R22344224K5FRJX5ZDCZL',
                'applepay',
                'Apple Pay',
                'https://checkoutshopper-live.adyen.com/checkoutshopper/images/logos/applepay.svg',
                true,
                ['ANY'],
                ['ANY'],
                'wallet'
            ),
            new PaymentMethod(
                'PM3224P2232222225FNZ7B8G595',
                'ideal',
                'Credit Card',
                'https://checkoutshopper-live.adyen.com/checkoutshopper/images/logos/card.svg',
                true,
                ['ANY'],
                ['ANY'],
                'creditOrDebitCard'
            ),
            new PaymentMethod(
                'PM3224R223224K5FR11JX5ZDCZL',
                'paypal',
                'Apple Pay',
                'https://checkoutshopper-live.adyen.com/checkoutshopper/images/logos/applepay.svg',
                true,
                ['EUR'],
                ['ANY'],
                'wallet'
            )
        ];
    }

    /**
     * @return PaymentMethod[]
     *
     * @throws PaymentMethodDataEmptyException
     * @throws InvalidPaymentMethodCodeException
     */
    private function getOneyMethod(): array
    {
        return [
            new PaymentMethod(
                'PM3224P6322322225FNZ7B8G595',
                'oney',
                'Credit Card',
                'https://checkoutshopper-live.adyen.com/checkoutshopper/images/logos/card.svg',
                true,
                ['ANY'],
                ['ANY'],
                'creditOrDebitCard',
                '',
                '',
                '',
                null,
                null,
                '',
                new Oney(['3', '4', '6'])
            )
        ];
    }

    /**
     * @return PaymentMethod[]
     *
     * @throws PaymentMethodDataEmptyException
     * @throws InvalidPaymentMethodCodeException
     */
    private function getUnsupportedMethod(): array
    {
        return [
            new PaymentMethod(
                'PM3224P6322322225FNZ7B8G595',
                'amazonpay',
                'Credit Card',
                'https://checkoutshopper-live.adyen.com/checkoutshopper/images/logos/card.svg',
                true,
                ['ANY'],
                ['ANY'],
                'creditOrDebitCard',
                '',
                '',
                '',
                null,
                null,
                '',
                new AmazonPay()
            )
        ];
    }
}
