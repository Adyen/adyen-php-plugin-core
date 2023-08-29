<?php

namespace Adyen\Core\Tests\BusinessLogic\CheckoutAPI\PaymentRequest;

use Adyen\Core\BusinessLogic\Bootstrap\SingleInstance;
use Adyen\Core\BusinessLogic\CheckoutAPI\CheckoutAPI;
use Adyen\Core\BusinessLogic\CheckoutAPI\PaymentRequest\Controller\PaymentRequestController;
use Adyen\Core\BusinessLogic\CheckoutAPI\PaymentRequest\Request\StartTransactionRequest;
use Adyen\Core\BusinessLogic\CheckoutAPI\PaymentRequest\Response\StartTransactionResponse;
use Adyen\Core\BusinessLogic\DataAccess\Connection\Repositories\ConnectionSettingsRepository;
use Adyen\Core\BusinessLogic\Domain\Checkout\AdyenGiving\Repositories\DonationsDataRepository;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\MissingActiveApiConnectionData;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Factory\PaymentRequestFactory;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Currency;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PaymentMethodCode;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\ResultCode;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\StartTransactionResult;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors\AmountProcessor;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors\MerchantIdProcessor;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors\PaymentRequestProcessorsRegistry;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors\ReferenceProcessor;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors\ReturnUrlProcessor;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors\StateDataProcessors\OriginStateDataProcessor;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Processors\StateDataProcessors\PaymentMethodStateDataProcessor;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Services\PaymentRequestService;
use Adyen\Core\BusinessLogic\Domain\Connection\Enums\Mode;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionSettings;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Services\TransactionHistoryService;
use Adyen\Core\Infrastructure\ServiceRegister;
use Adyen\Core\Tests\BusinessLogic\AdminAPI\Store\MockComponents\MockConnectionSettingsRepository;
use Adyen\Core\Tests\BusinessLogic\CheckoutAPI\PaymentRequest\MockComponents\MockPaymentRequestProcessor;
use Adyen\Core\Tests\BusinessLogic\CheckoutAPI\PaymentRequest\MockComponents\MockStartTransactionProxy;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class PaymentRequestApiTest
 *
 * @package Adyen\Core\Tests\BusinessLogic\CheckoutAPI\PaymentRequest
 */
class PaymentRequestApiTest extends BaseTestCase
{
    /**
     * @var MockStartTransactionProxy
     */
    private $paymentsProxy;
    /**
     * @var MockConnectionSettingsRepository
     */
    private $connectionSettingsRepo;

    public function setUp(): void
    {
        parent::setUp();

        $this->paymentsProxy = new MockStartTransactionProxy();
        $this->connectionSettingsRepo = new MockConnectionSettingsRepository();

        TestServiceRegister::registerService(
            PaymentRequestController::class,
            new SingleInstance(function () {
                return new PaymentRequestController(new PaymentRequestService(
                    $this->paymentsProxy,
                    new PaymentRequestFactory(),
                    TestServiceRegister::getService(DonationsDataRepository::class),
                    TestServiceRegister::getService(TransactionHistoryService::class)
                ));
            })
        );

        TestServiceRegister::registerService(
            ConnectionSettingsRepository::class,
            new SingleInstance(function () {
                return $this->connectionSettingsRepo;
            })
        );

        TestServiceRegister::registerService(
            AmountProcessor::class,
            new SingleInstance(static function () {
                return new AmountProcessor();
            })
        );
        TestServiceRegister::registerService(
            ReferenceProcessor::class,
            new SingleInstance(static function () {
                return new ReferenceProcessor();
            })
        );
        TestServiceRegister::registerService(
            ReturnUrlProcessor::class,
            new SingleInstance(static function () {
                return new ReturnUrlProcessor();
            })
        );
        TestServiceRegister::registerService(
            MerchantIdProcessor::class,
            new SingleInstance(static function () {
                return new MerchantIdProcessor(ServiceRegister::getService(ConnectionSettingsRepository::class));
            })
        );
        TestServiceRegister::registerService(
            PaymentMethodStateDataProcessor::class,
            new SingleInstance(static function () {
                return new PaymentMethodStateDataProcessor();
            })
        );
        TestServiceRegister::registerService(
            OriginStateDataProcessor::class,
            new SingleInstance(static function () {
                return new OriginStateDataProcessor();
            })
        );

        PaymentRequestProcessorsRegistry::registerGlobal(AmountProcessor::class);
        PaymentRequestProcessorsRegistry::registerGlobal(ReferenceProcessor::class);
        PaymentRequestProcessorsRegistry::registerGlobal(ReturnUrlProcessor::class);
        PaymentRequestProcessorsRegistry::registerGlobal(MerchantIdProcessor::class);
        PaymentRequestProcessorsRegistry::registerGlobal(PaymentMethodStateDataProcessor::class);
        PaymentRequestProcessorsRegistry::registerGlobal(OriginStateDataProcessor::class);

    }

    /**
     * @dataProvider successfulResultCodesProvider
     *
     * @return void
     * @throws \Exception
     */
    public function testSuccessfulPaymentTransactionStarting(ResultCode $successfullyResultCode): void
    {
        // Arrange
        $expectedResult = new StartTransactionResult(
            $successfullyResultCode, 'TEST-PSP-REFERENCE-001', ['test' => 'action']
        );
        $this->paymentsProxy->setMockResult($expectedResult);

        $expectedAmount = Amount::fromFloat(456.12, Currency::getDefault());
        $paymentRequest = new StartTransactionRequest(
            'ideal', $expectedAmount, 'ref1', 'https://example.com', []
        );

        // Act
        $response = CheckoutAPI::get()->paymentRequest('store1')->startTransaction($paymentRequest);

        // Assert
        self::assertTrue($response->isSuccessful());
        self::assertEquals(new StartTransactionResponse($expectedResult), $response);
        self::assertEquals($expectedResult->getPspReference(), $response->getPspReference());
        self::assertEquals($expectedResult->getAction(), $response->getAction());
        self::assertTrue($response->isAdditionalActionRequired());
        self::assertEquals([
            'resultCode' => $expectedResult->getResultCode(),
            'pspReference' => $expectedResult->getPspReference(),
            'action' => $expectedResult->getAction(),
        ], $response->toArray());
        self::assertTrue($this->paymentsProxy->getIsCalled());
        self::assertEquals($expectedAmount, $this->paymentsProxy->getLastRequest()->getAmount());
        self::assertEquals('ref1', $this->paymentsProxy->getLastRequest()->getReference());
        self::assertEquals('https://example.com', $this->paymentsProxy->getLastRequest()->getReturnUrl());
    }

    /**
     * @dataProvider unsuccessfulResultCodesProvider
     *
     * @return void
     * @throws \Exception
     */
    public function testUnsuccessfulUpdatePaymentDetails(ResultCode $unsuccessfullyResultCode): void
    {
        // Arrange
        $expectedResult = new StartTransactionResult($unsuccessfullyResultCode, 'TEST-PSP-REFERENCE-001');
        $this->paymentsProxy->setMockResult($expectedResult);
        $expectedAmount = Amount::fromFloat(456.12, Currency::getDefault());
        $paymentRequest = new StartTransactionRequest(
            'ideal', $expectedAmount, 'ref1', 'https://example.com', []
        );

        // Act
        $response = CheckoutAPI::get()->paymentRequest('store1')->startTransaction($paymentRequest);

        // Assert
        self::assertFalse($response->isSuccessful());
        self::assertEquals(new StartTransactionResponse($expectedResult), $response);
        self::assertEquals('TEST-PSP-REFERENCE-001', $response->getPspReference());
        self::assertTrue($this->paymentsProxy->getIsCalled());
    }

    public function testPaymentTransactionStartingProcessPaymentStateData(): void
    {
        // Arrange
        $expectedResult = new StartTransactionResult(ResultCode::authorised());
        $this->paymentsProxy->setMockResult($expectedResult);

        $stateData = [
            'paymentMethod' => [
                'type' => "scheme",
                'encryptedCardNumber' => 'test_4111111111111111',
                'encryptedExpiryMonth' => 'test_03',
                'encryptedExpiryYear' => 'test_2030',
                'encryptedSecurityCode' => 'test_737',
            ],
            'origin' => 'http://example.com/test',
            'reference' => 'Invalid state data value that should not be in the final request',
        ];
        $paymentRequest = new StartTransactionRequest(
            'scheme', Amount::fromFloat(456.12, Currency::getDefault()), 'ref1', 'https://example.com', $stateData
        );

        // Act
        $response = CheckoutAPI::get()->paymentRequest('store1')->startTransaction($paymentRequest);

        // Assert
        self::assertTrue($response->isSuccessful());
        self::assertEquals(new StartTransactionResponse($expectedResult), $response);
        self::assertTrue($this->paymentsProxy->getIsCalled());
        self::assertEquals($stateData['paymentMethod'], $this->paymentsProxy->getLastRequest()->getPaymentMethod());
        self::assertEquals($stateData['origin'], $this->paymentsProxy->getLastRequest()->getOrigin());
        self::assertNotEquals($stateData['reference'], $this->paymentsProxy->getLastRequest()->getReference());
        self::assertEquals('ref1', $this->paymentsProxy->getLastRequest()->getReference());
    }

    public function testPaymentTransactionStartingMerchantIdProcessing(): void
    {
        // Arrange
        $expectedResult = new StartTransactionResult(ResultCode::authorised());
        $this->paymentsProxy->setMockResult($expectedResult);

        $storeId = 'store1';
        $expectedMerchantId = 'TestMerchantIdECOM';

        $this->connectionSettingsRepo->setConnectionSettings(new ConnectionSettings(
            $storeId,
            Mode::MODE_TEST,
            new ConnectionData('1', $expectedMerchantId),
            null
        ));

        // Act
        $response = CheckoutAPI::get()->paymentRequest($storeId)->startTransaction(new StartTransactionRequest(
            'scheme', Amount::fromFloat(456.12, Currency::getDefault()), 'ref1', 'https://example.com', []
        ));

        // Assert
        self::assertTrue($response->isSuccessful());
        self::assertTrue($this->paymentsProxy->getIsCalled());
        self::assertEquals($expectedMerchantId, $this->paymentsProxy->getLastRequest()->getMerchantId());
    }

    public function testPaymentTransactionStartingWithMissingConnectionData(): void
    {
        // Arrange
        $this->connectionSettingsRepo->setMockConnectionSettings(null);

        // Assert
        $this->expectException(MissingActiveApiConnectionData::class);

        // Act
        TestServiceRegister::getService(PaymentRequestController::class)->startTransaction(
            new StartTransactionRequest(
                'scheme', Amount::fromFloat(456.12, Currency::getDefault()), 'ref1', 'https://example.com', []
            )
        );
    }

    public function testOnlyPaymentTypeSpecificProcessorIsExecuted(): void
    {
        // Arrange
        $expectedResult = new StartTransactionResult(ResultCode::authorised());
        $this->paymentsProxy->setMockResult($expectedResult);


        $expectedTestData = ['mock' => 'data'];
        TestServiceRegister::registerService(
            MockPaymentRequestProcessor::class,
            new SingleInstance(static function () use ($expectedTestData) {
                return new MockPaymentRequestProcessor($expectedTestData);
            })
        );
        TestServiceRegister::registerService(
            'OutOfScopeProcessor',
            new SingleInstance(static function () use ($expectedTestData) {
                return new MockPaymentRequestProcessor($expectedTestData);
            })
        );
        PaymentRequestProcessorsRegistry::registerByPaymentType(
            PaymentMethodCode::parse('scheme'), MockPaymentRequestProcessor::class
        );
        PaymentRequestProcessorsRegistry::registerByPaymentType(
            PaymentMethodCode::parse('afterpay_default'), 'OutOfScopeProcessor'
        );

        // Act
        $response = CheckoutAPI::get()->paymentRequest('store1')
            ->startTransaction(new StartTransactionRequest(
                'scheme', Amount::fromFloat(456.12, Currency::getDefault()), 'ref1', 'https://example.com', []
            ));

        // Assert
        self::assertTrue($response->isSuccessful());
        self::assertTrue($this->paymentsProxy->getIsCalled());
        self::assertEquals(
            $expectedTestData,
            $this->paymentsProxy->getLastRequest()->getPaymentMethod()['mockData']
        );
    }

    public function successfulResultCodesProvider(): array
    {
        return [
            [ResultCode::authorised()],
            [ResultCode::pending()],
            [ResultCode::presentToShopper()],
            [ResultCode::received()],
        ];
    }

    public function unsuccessfulResultCodesProvider(): array
    {
        return [
            [ResultCode::refused()],
            [ResultCode::error()],
        ];
    }
}
