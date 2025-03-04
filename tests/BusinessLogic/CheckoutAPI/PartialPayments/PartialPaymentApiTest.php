<?php

namespace Adyen\Core\Tests\BusinessLogic\CheckoutAPI\PartialPayments;

use Adyen\Core\BusinessLogic\AdyenAPI\PartialPayments\Http\Proxy;
use Adyen\Core\BusinessLogic\CheckoutAPI\CheckoutAPI;
use Adyen\Core\BusinessLogic\CheckoutAPI\PartialPayment\Request\BalanceCheckRequest;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Currency;
use Adyen\Core\BusinessLogic\Domain\PartialPayments\Models\BalanceResult;
use Adyen\Core\BusinessLogic\Domain\PartialPayments\Proxies\PartialPaymentProxy;
use Adyen\Core\Infrastructure\Http\HttpClient;
use Adyen\Core\Infrastructure\Http\HttpResponse;
use Adyen\Core\Tests\BusinessLogic\AdminAPI\Store\MockComponents\MockConnectionSettingsRepository;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\TestHttpClient;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;

class PartialPaymentApiTest extends BaseTestCase
{
    /**
     * @var MockConnectionSettingsRepository
     */
    private $connectionSettingsRepo;
    /**
     * @var TestHttpClient
     */
    private $httpClient;

    public function setUp(): void
    {
        parent::setUp();

        $this->connectionSettingsRepo = new MockConnectionSettingsRepository();
        $this->httpClient = new TestHttpClient();
        TestServiceRegister::registerService(HttpClient::class, function () {
            return $this->httpClient;
        });

        TestServiceRegister::registerService(
            PartialPaymentProxy::class,
            function () {
                return new Proxy($this->httpClient, 'https://checkout-test.adyen.com', 'v71', '0123456789');
            }
        );
    }

    public function testBalanceCheck()
    {
        // arrange
        $this->httpClient->setMockResponses([
            new HttpResponse(
                200, array(), file_get_contents(__DIR__ . '/../../Common/ApiResponses/PartialPayments/balance.json')
            ),
        ]);

        // act
        $result = CheckoutAPI::get()->partialPayment('1')->checkBalance(
            new BalanceCheckRequest(
                100.12, 'EUR', ['type' => 'givex', 'cvc' => 737, 'number' => '4126491073027401']
            )
        );

        // assert
        $this->assertEquals('FKSPNCQ8HXSKGK82', $result->getPspReference());
        $this->assertEquals(Amount::fromInt(5000, Currency::fromIsoCode('EUR')), $result->getBalance());
        $this->assertEquals('NotEnoughBalance', $result->getResultCode());
    }
}
