<?php

namespace Adyen\Core\Tests\BusinessLogic\AdyenAPI\Checkout\Donations\Http;

use Adyen\Core\BusinessLogic\AdyenAPI\Checkout\Donations\Http\Proxy;
use Adyen\Core\BusinessLogic\Domain\Checkout\AdyenGiving\Models\DonationRequest;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Currency;
use Adyen\Core\Infrastructure\Http\HttpClient;
use Adyen\Core\Infrastructure\Http\HttpResponse;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Adyen\Core\Tests\Infrastructure\Common\TestComponents\TestHttpClient;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;

class ProxyTest extends BaseTestCase
{
    public $proxy;
    public $httpClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->httpClient = new TestHttpClient();
        TestServiceRegister::registerService(HttpClient::class, function () {
            return $this->httpClient;
        });

        $this->proxy = new Proxy($this->httpClient, 'https://checkout-test.adyen.com', 'v69', '0123456789');
    }

    public function testMakeDonationUrl(): void
    {
        // arrange
        $request = new DonationRequest(
            'YOUR_DONATION_TOKEN',
            Amount::fromInt(123, Currency::fromIsoCode('EUR')),
            'scheme',
            '991559660454807J',
            'CHARITY_ACCOUNT',
            'TEST',
            'https://testurl.com'
        );
        $this->httpClient->setMockResponses([new HttpResponse(200, [], json_encode(['status' => 'completed']))]);

        // act
        $this->proxy->makeDonation($request);

        // assert
        $history = $this->httpClient->getLastRequest();
        self::assertEquals('https://checkout-test.adyen.com/v69/donations', $history['url']);
    }

    public function testMakeDonationMethod(): void
    {
        // arrange
        $request = new DonationRequest(
            'YOUR_DONATION_TOKEN',
            Amount::fromInt(123, Currency::fromIsoCode('EUR')),
            'scheme',
            '991559660454807J',
            'CHARITY_ACCOUNT',
            'TEST',
            'https://testurl.com'
        );
        $this->httpClient->setMockResponses([new HttpResponse(200, [], json_encode(['status' => 'completed']))]);

        // act
        $this->proxy->makeDonation($request);

        // assert
        $history = $this->httpClient->getLastRequest();
        self::assertEquals(HttpClient::HTTP_METHOD_POST, $history['method']);
    }

    public function testMakeDonationBody(): void
    {
        // arrange
        $request = new DonationRequest(
            'YOUR_DONATION_TOKEN',
            Amount::fromInt(123, Currency::fromIsoCode('EUR')),
            'scheme',
            '991559660454807J',
            'CHARITY_ACCOUNT',
            'TEST',
            'https://testurl.com'
        );
        $this->httpClient->setMockResponses([new HttpResponse(200, [], json_encode(['status' => 'completed']))]);

        // act
        $this->proxy->makeDonation($request);

        // assert
        $requestArray = [
            'donationToken' => 'YOUR_DONATION_TOKEN',
            'amount' => [
                'currency' => 'EUR',
                'value' => 123,
            ],
            'paymentMethod' => [
                'type' => 'scheme'
            ],
            'donationOriginalPspReference' => '991559660454807J',
            'reference' => '991559660454807J',
            'donationAccount' => 'CHARITY_ACCOUNT',
            'shopperInteraction' => 'ContAuth',
            'merchantAccount' => 'TEST',
            'returnUrl' => 'https://testurl.com'
        ];

        $history = $this->httpClient->getLastRequest();
        self::assertEquals(json_encode($requestArray), $history['body']);
    }
}
