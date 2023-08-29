<?php

namespace Adyen\Core\Tests\BusinessLogic\CheckoutAPI\CheckoutConfig;

use Adyen\Core\BusinessLogic\Bootstrap\SingleInstance;
use Adyen\Core\BusinessLogic\CheckoutAPI\CheckoutAPI;
use Adyen\Core\BusinessLogic\CheckoutAPI\CheckoutConfig\Controller\CheckoutConfigController;
use Adyen\Core\BusinessLogic\CheckoutAPI\CheckoutConfig\Request\DisableStoredDetailsRequest;
use Adyen\Core\BusinessLogic\CheckoutAPI\CheckoutConfig\Request\PaymentCheckoutConfigRequest;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Currency;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\AvailablePaymentMethodsResponse;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Country;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PaymentMethodCode;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PaymentMethodResponse;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Proxies\StoredDetailsProxy;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Services\PaymentCheckoutConfigService;
use Adyen\Core\BusinessLogic\Domain\Connection\Enums\Mode;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionSettings;
use Adyen\Core\BusinessLogic\Domain\Connection\Repositories\ConnectionSettingsRepository;
use Adyen\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use Adyen\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\MethodAdditionalData\ApplePay;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\MethodAdditionalData\CardConfig;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\MethodAdditionalData\GooglePay;
use Adyen\Core\BusinessLogic\Domain\Payment\Models\PaymentMethod;
use Adyen\Core\BusinessLogic\Domain\Payment\Repositories\PaymentMethodConfigRepository;
use Adyen\Core\Tests\BusinessLogic\AdminAPI\Store\MockComponents\MockConnectionSettingsRepository;
use Adyen\Core\Tests\BusinessLogic\CheckoutAPI\CheckoutConfig\MockComponents\MockPaymentsProxy;
use Adyen\Core\Tests\BusinessLogic\CheckoutAPI\CheckoutConfig\MockComponents\MockStoredDetailsProxy;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class PaymentCheckoutConfigApiTest
 *
 * @package Adyen\Core\Tests\BusinessLogic\CheckoutAPI\CheckoutConfig
 */
class PaymentCheckoutConfigApiTest extends BaseTestCase
{
    /**
     * @var MockConnectionSettingsRepository
     */
    private $connectionSettingsRepo;
    /**
     * @var PaymentMethodConfigRepository
     */
    private $paymentMethodConfigRepo;
    /**
     * @var MockPaymentsProxy
     */
    private $paymentsProxy;
    /**
     * @var StoredDetailsProxy
     */
    private $storedDetailsProxy;

    public function setUp(): void
    {
        parent::setUp();

        $this->connectionSettingsRepo = new MockConnectionSettingsRepository();
        $this->paymentMethodConfigRepo = TestServiceRegister::getService(PaymentMethodConfigRepository::class);
        $this->paymentsProxy = new MockPaymentsProxy();
        $this->storedDetailsProxy = new MockStoredDetailsProxy();

        TestServiceRegister::registerService(
            CheckoutConfigController::class,
            new SingleInstance(function () {
                return new CheckoutConfigController(new PaymentCheckoutConfigService(
                    $this->connectionSettingsRepo,
                    $this->paymentMethodConfigRepo,
                    $this->paymentsProxy,
                    $this->storedDetailsProxy,
                    TestServiceRegister::getService(ConnectionService::class)
                ));
            })
        );
    }

    public function testPaymentCheckoutConfigWithoutValidClientKey()
    {
        // Arrange
        $request = new PaymentCheckoutConfigRequest(
            Amount::fromFloat(123.23, Currency::getDefault())
        );

        // Act
        $response = CheckoutAPI::get()->checkoutConfig('store1')->getPaymentCheckoutConfig($request);

        // Assert
        self::assertFalse($response->isSuccessful());
        self::assertStringContainsString('Invalid configuration', $response->toArray()['errorMessage']);
    }

    public function testPaymentCheckoutConfigWithoutValidCredentials()
    {
        // Arrange
        $this->connectionSettingsRepo->setMockConnectionSettings(null);
        $request = new PaymentCheckoutConfigRequest(
            Amount::fromFloat(123.23, Currency::getDefault())
        );

        // Act
        $response = CheckoutAPI::get()->checkoutConfig('store1')->getPaymentCheckoutConfig($request);

        // Assert
        self::assertFalse($response->isSuccessful());
        self::assertStringContainsString('Invalid merchant configuration', $response->toArray()['errorMessage']);
    }

    public function testPaymentCheckoutConfig()
    {
        // Arrange
        StoreContext::doWithStore('store1', function () {
            $this->connectionSettingsRepo->setConnectionSettings(
                new ConnectionSettings('store1', 'test', new ConnectionData('01234567', '1234', '', 'test-client-key'), null)
            );
        });
        $request = new PaymentCheckoutConfigRequest(
            Amount::fromFloat(123.23, Currency::getDefault()), Country::fromIsoCode('DE')
        );

        // Act
        $response = CheckoutAPI::get()->checkoutConfig('store1')->getPaymentCheckoutConfig($request);

        // Assert
        self::assertTrue($response->isSuccessful());
        self::assertEquals([], $response->getPaymentMethodResponse());
        self::assertEquals([], $response->getPaymentMethodsConfiguration());
        self::assertEquals('en-US', $response->toArray()['locale']);
        self::assertEquals(Mode::MODE_TEST, $response->toArray()['environment']);
        self::assertEquals('test-client-key', $response->toArray()['clientKey']);
        self::assertEquals('test-client-key', $response->toArray()['clientKey']);
        self::assertEquals(12323, $response->toArray()['amount']['value']);
        self::assertEquals(Currency::getDefault()->getIsoCode(), $response->toArray()['amount']['currency']);
        self::assertEquals('DE', $response->toArray()['countryCode']);
    }

    public function testPaymentCheckoutConfigForPaymentMethods()
    {
        // Arrange
        StoreContext::doWithStore('store1', function () {
            $this->connectionSettingsRepo->setConnectionSettings(
                new ConnectionSettings('store1', 'test', new ConnectionData('01234567', '1234', '', 'test-client-key'), null)
            );
        });
        $this->paymentsProxy->setMockResult(new AvailablePaymentMethodsResponse(
            [new PaymentMethodResponse('test', 'scheme')],
            [new PaymentMethodResponse('test', 'scheme')]
        ));
        $request = new PaymentCheckoutConfigRequest(
            Amount::fromFloat(123.23, Currency::getDefault())
        );

        // Act
        $response = CheckoutAPI::get()->checkoutConfig('store1')->getPaymentCheckoutConfig($request);

        // Assert
        self::assertTrue($response->isSuccessful());
        self::assertCount(1, $response->getPaymentMethodResponse());
        self::assertEquals(new PaymentMethodResponse('test', 'scheme'), $response->getPaymentMethodResponse()[0]);
        self::assertCount(1, $response->getStoredPaymentMethodResponse());
        self::assertEquals(new PaymentMethodResponse('test', 'scheme'), $response->getStoredPaymentMethodResponse()[0]);
    }

    public function testPaymentCheckoutConfigForPaymentConfigs()
    {
        // Arrange
        $expectedMethodConfig = new PaymentMethod(
            'test', 'scheme', 'test', 'hhtp://test.example.com', true, [], [], 'cards'
        );
        $expectedMethodConfig->setAdditionalData(new CardConfig());

        StoreContext::doWithStore('store1', function () use ($expectedMethodConfig) {
            $this->connectionSettingsRepo->setConnectionSettings(
                new ConnectionSettings('store1', 'test', new ConnectionData('01234567', '1234', '', 'test-client-key'), null)
            );
            $this->paymentMethodConfigRepo->saveMethodConfiguration($expectedMethodConfig);
        });

        $request = new PaymentCheckoutConfigRequest(
            Amount::fromFloat(123.23, Currency::getDefault())
        );

        // Act
        $response = CheckoutAPI::get()->checkoutConfig('store1')->getPaymentCheckoutConfig($request);

        // Assert
        self::assertTrue($response->isSuccessful());
        self::assertCount(1, $response->getPaymentMethodsConfiguration());
        self::assertEquals($expectedMethodConfig, $response->getPaymentMethodsConfiguration()[0]);
    }

    public function testExpressPaymentCheckoutConfig(): void
    {
        // Arrange
        $disabledExpressCheckoutMethodConfig = new PaymentMethod(
            'applePay', (string)PaymentMethodCode::applePay(),
            'applePay', 'hhtp://test.example.com', true, [], [],
            'wallet', '', '', '', 0, 0, 'hhtp://test.example.com',
            new ApplePay()
        );
        $enabledExpressCheckoutMethodConfig = new PaymentMethod(
            'paywithgoogle', (string)PaymentMethodCode::payWithGoogle(),
            'paywithgoogle', 'hhtp://test.example.com', true, [], [],
            'wallet', '', '', '', 0, 0, 'hhtp://test.example.com',
            new GooglePay('', '', true)
        );

        StoreContext::doWithStore('store1', function () use ($disabledExpressCheckoutMethodConfig) {
            $this->connectionSettingsRepo->setConnectionSettings(
                new ConnectionSettings('store1', 'test', new ConnectionData('01234567', '1234', '', 'test-client-key'), null)
            );
            $this->paymentMethodConfigRepo->saveMethodConfiguration($disabledExpressCheckoutMethodConfig);
        });
        StoreContext::doWithStore('store1', function () use ($enabledExpressCheckoutMethodConfig) {
            $this->connectionSettingsRepo->setConnectionSettings(
                new ConnectionSettings('store1', 'test', new ConnectionData('01234567', '1234', '', 'test-client-key'), null)
            );
            $this->paymentMethodConfigRepo->saveMethodConfiguration($enabledExpressCheckoutMethodConfig);
        });

        $request = new PaymentCheckoutConfigRequest(
            Amount::fromFloat(123.23, Currency::getDefault())
        );

        // Act
        $response = CheckoutAPI::get()->checkoutConfig('store1')->getExpressPaymentCheckoutConfig($request);

        // Assert
        self::assertTrue($response->isSuccessful());
        self::assertCount(1, $response->getPaymentMethodsConfiguration());
        self::assertEquals($enabledExpressCheckoutMethodConfig, $response->getPaymentMethodsConfiguration()[0]);
    }

    public function testDisableStoredDetails(): void
    {
        // arrange
        /** @var ConnectionSettingsRepository $repo */
        $repo = TestServiceRegister::getService(ConnectionSettingsRepository::class);
        $repo->setConnectionSettings(
            new ConnectionSettings('store1', 'test', new ConnectionData('01234567', '1234', '', 'test-client-key'), null)
        );

        // act
        $response = CheckoutAPI::get()->checkoutConfig('store1')->disableStoredDetails(
            new DisableStoredDetailsRequest('012', '012')
        );

        // assert
        self::assertTrue($response->isSuccessful());
    }

    public function testDisableStoredDetailsProxyFails(): void
    {
        // arrange
        /** @var ConnectionSettingsRepository $repo */
        $repo = TestServiceRegister::getService(ConnectionSettingsRepository::class);
        $repo->setConnectionSettings(
            new ConnectionSettings(
                'store1',
                'test',
                new ConnectionData('01234567', '1234', '', 'test-client-key'),
                null
            )
        );
        $this->storedDetailsProxy->isSuccessful = false;

        // act
        $response = CheckoutAPI::get()->checkoutConfig('store1')->disableStoredDetails(
            new DisableStoredDetailsRequest('012', '012')
        );

        // assert
        self::assertFalse($response->isSuccessful());
        self::assertEquals('Unhandled error occurred: Exception', $response->toArray()['errorMessage']);
    }

    public function testDisableStoredDetailsInvalidCredentials()
    {
        // act
        $response = CheckoutAPI::get()->checkoutConfig('store1')->disableStoredDetails(
            new DisableStoredDetailsRequest('012', '012')
        );

        // assert
        self::assertFalse($response->isSuccessful());
        self::assertEquals('Connection settings not found.', $response->toArray()['errorMessage']);
    }
}
