<?php

namespace Adyen\Core\Tests\BusinessLogic\CheckoutAPI\PaymentRequest;

use Adyen\Core\BusinessLogic\Bootstrap\SingleInstance;
use Adyen\Core\BusinessLogic\CheckoutAPI\CheckoutAPI;
use Adyen\Core\BusinessLogic\CheckoutAPI\PaymentRequest\Controller\PaymentRequestController;
use Adyen\Core\BusinessLogic\CheckoutAPI\PaymentRequest\Response\UpdatePaymentDetailsResponse;
use Adyen\Core\BusinessLogic\DataAccess\Connection\Repositories\ConnectionSettingsRepository;
use Adyen\Core\BusinessLogic\Domain\Checkout\AdyenGiving\Repositories\DonationsDataRepository;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Factory\PaymentRequestFactory;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\ResultCode;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\UpdatePaymentDetailsResult;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Services\PaymentRequestService;
use Adyen\Core\BusinessLogic\Domain\TransactionHistory\Services\TransactionHistoryService;
use Adyen\Core\Tests\BusinessLogic\AdminAPI\Store\MockComponents\MockConnectionSettingsRepository;
use Adyen\Core\Tests\BusinessLogic\CheckoutAPI\PaymentRequest\MockComponents\MockUpdatePaymentDetailsProxy;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class PaymentUpdateApiTest
 *
 * @package Adyen\Core\Tests\BusinessLogic\CheckoutAPI
 */
class UpdatePaymentDetailsApiTest extends BaseTestCase
{
    /**
     * @var MockUpdatePaymentDetailsProxy
     */
    private $paymentsProxy;
    /**
     * @var MockConnectionSettingsRepository
     */
    private $connectionSettingsRepo;

    public function setUp(): void
    {
        parent::setUp();

        $this->paymentsProxy = new MockUpdatePaymentDetailsProxy();
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
    }

    /**
     * @dataProvider successfulResultCodesProvider
     *
     * @return void
     * @throws \Exception
     */
    public function testSuccessfulUpdatePaymentDetails(ResultCode $successfullyResultCode): void
    {
        // Arrange
        $expectedResult = new UpdatePaymentDetailsResult($successfullyResultCode, 'TEST-PSP-REFERENCE-001');
        $this->paymentsProxy->setMockResult($expectedResult);
        $rawUpdateData = [];

        // Act
        $response = CheckoutAPI::get()->paymentRequest('store1')->updatePaymentDetails($rawUpdateData);

        // Assert
        self::assertTrue($response->isSuccessful());
        self::assertEquals(new UpdatePaymentDetailsResponse($expectedResult), $response);
        self::assertEquals([
            'resultCode' => (string)$successfullyResultCode, 'pspReference' => $expectedResult->getPspReference()
        ], $response->toArray());
        self::assertEquals('TEST-PSP-REFERENCE-001', $response->getPspReference());
        self::assertTrue($this->paymentsProxy->getIsCalled());
    }

    /**
     * @dataProvider unsuccessfulResultCodesProvider
     *
     * @return void
     * @throws \Exception
     */
    public function testUnsuccessfulUpdatePaymentDetails(ResultCode $unsuccessfullyResultCode)
    {
        // Arrange
        $expectedResult = new UpdatePaymentDetailsResult($unsuccessfullyResultCode, 'TEST-PSP-REFERENCE-001');
        $this->paymentsProxy->setMockResult($expectedResult);
        $rawUpdateData = [];

        // Act
        $response = CheckoutAPI::get()->paymentRequest('store1')->updatePaymentDetails($rawUpdateData);

        // Assert
        self::assertFalse($response->isSuccessful());
        self::assertEquals(new UpdatePaymentDetailsResponse($expectedResult), $response);
        self::assertEquals('TEST-PSP-REFERENCE-001', $response->getPspReference());
        self::assertTrue($this->paymentsProxy->getIsCalled());
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
