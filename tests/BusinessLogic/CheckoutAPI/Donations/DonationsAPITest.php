<?php

namespace Adyen\Core\Tests\BusinessLogic\CheckoutAPI\Donations;

use Adyen\Core\BusinessLogic\CheckoutAPI\CheckoutAPI;
use Adyen\Core\BusinessLogic\CheckoutAPI\Donations\Controller\DonationController;
use Adyen\Core\BusinessLogic\CheckoutAPI\Donations\Request\MakeDonationRequest;
use Adyen\Core\BusinessLogic\CheckoutAPI\Donations\Response\MakeDonationResponse;
use Adyen\Core\BusinessLogic\Domain\AdyenGivingSettings\Models\AdyenGivingSettings;
use Adyen\Core\BusinessLogic\Domain\AdyenGivingSettings\Services\AdyenGivingSettingsService;
use Adyen\Core\BusinessLogic\Domain\Checkout\AdyenGiving\Models\DonationsData;
use Adyen\Core\BusinessLogic\Domain\Checkout\AdyenGiving\Repositories\DonationsDataRepository;
use Adyen\Core\BusinessLogic\Domain\Checkout\AdyenGiving\Services\DonationsService;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionSettings;
use Adyen\Core\BusinessLogic\Domain\Connection\Repositories\ConnectionSettingsRepository;
use Adyen\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use Adyen\Core\BusinessLogic\Domain\Integration\Order\OrderService;
use Adyen\Core\BusinessLogic\Domain\Integration\Webhook\WebhookUrlService;
use Adyen\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Adyen\Core\Tests\BusinessLogic\CheckoutAPI\Donations\MockComponents\MockDonationsProxy;
use Adyen\Core\Tests\BusinessLogic\Common\BaseTestCase;
use Adyen\Core\Tests\Infrastructure\Common\TestServiceRegister;

class DonationsAPITest extends BaseTestCase
{
    /**
     * @var DonationController
     */
    public $controller;
    /**
     * @var MockDonationsProxy
     */
    public $proxy;
    /**
     * @var AdyenGivingSettingsService
     */
    public $givingSettingsService;
    /**
     * @var ConnectionSettingsRepository
     */
    public $connectionRepository;
    /**
     * @var DonationsDataRepository
     */
    public $dataRepository;
    protected function setUp(): void
    {
        parent::setUp();

        $this->proxy = new MockDonationsProxy();
        $this->controller = new DonationController(
            new DonationsService(
                $this->proxy,
                TestServiceRegister::getService(AdyenGivingSettingsService::class),
                TestServiceRegister::getService(ConnectionService::class),
                TestServiceRegister::getService(DonationsDataRepository::class),
                TestServiceRegister::getService(OrderService::class),
                TestServiceRegister::getService(WebhookUrlService::class)
            )
        );

        TestServiceRegister::registerService(
            DonationController::class,
            function () {
                return $this->controller;
            }
        );
        $this->givingSettingsService = TestServiceRegister::getService(AdyenGivingSettingsService::class);
        $this->connectionRepository = TestServiceRegister::getService(ConnectionSettingsRepository::class);
        $this->dataRepository = TestServiceRegister::getService(DonationsDataRepository::class);
    }

    public function testMakeDonationSuccess(): void
    {
        // arrange
        $settings = new ConnectionSettings(
            'store1',
            'test',
            new ConnectionData('1234567890', ''),
            null
        );
        StoreContext::doWithStore('store1', [$this->connectionRepository, 'setConnectionSettings'], [$settings]);
        StoreContext::doWithStore(
            'store1',
            [$this->givingSettingsService, 'saveAdyenGivingSettings'],
            [
                new AdyenGivingSettings(
                    true,
                    'CHARITY_ACCOUNT',
                    'TEST',
                    'TEST',
                    [2, 3, 4],
                    'www.test.com',
                    'www.test.com'
                )
            ]
        );
        $donationData = new DonationsData('TEST', '01234567', '012345678', 'scheme');
        StoreContext::doWithStore('store1', [$this->dataRepository, 'saveDonationsData'], [$donationData]);
        $donationRequest = new MakeDonationRequest(
            123,
            'EUR',
            'TEST'
        );

        // act
        $result = CheckoutAPI::get()->donation('store1')->makeDonation($donationRequest);

        // assert
        self::assertEquals(new MakeDonationResponse('completed'), $result);
    }

    public function testMakeDonationFails(): void
    {
        // arrange
        $settings = new ConnectionSettings(
            'store1',
            'test',
            new ConnectionData('1234567890', ''),
            null
        );
        StoreContext::doWithStore('store1', [$this->connectionRepository, 'setConnectionSettings'], [$settings]);
        StoreContext::doWithStore(
            'store1',
            [$this->givingSettingsService, 'saveAdyenGivingSettings'],
            [
                new AdyenGivingSettings(
                    true,
                    'CHARITY_ACCOUNT',
                    'TEST',
                    'TEST',
                    [2, 3, 4],
                    'www.test.com',
                    'www.test.com'
                )
            ]
        );
        $donationData = new DonationsData('TEST', '01234567', '012345678', 'scheme');
        StoreContext::doWithStore('store1', [$this->dataRepository, 'saveDonationsData'], [$donationData]);
        $donationRequest = new MakeDonationRequest(
            123,
            'EUR',
            'TEST'
        );
        $this->proxy->isSuccessful = false;

        // act
        $result = CheckoutAPI::get()->donation('store1')->makeDonation($donationRequest);

        // assert
        self::assertFalse($result->isSuccessful());
    }

    public function testGetSettingsNoSettings(): void
    {
        // act
        $result = CheckoutAPI::get()->donation('store1')->getDonationSettings('TEST', '1');

        // assert
        self::assertTrue($result->isSuccessful());
        self::assertEmpty($result->toArray());
    }

    public function testGetSettingsNoDonationData(): void
    {
        // arrange
        $settings = new AdyenGivingSettings(
            true,
            'CHARITY_NAME',
            'description',
            'account',
            [2, 3, 4],
            'website',
            'logo',
            'image'
        );
        StoreContext::doWithStore('store1', [$this->givingSettingsService, 'saveAdyenGivingSettings'], [$settings]);
        $settings = new ConnectionSettings(
            'store1',
            'test',
            new ConnectionData('1234567890', ''),
            null
        );
        StoreContext::doWithStore('store1', [$this->connectionRepository, 'setConnectionSettings'], [$settings]);

        // act
        $result = CheckoutAPI::get()->donation('store1')->getDonationSettings('TEST', '1');

        // assert
        self::assertTrue($result->isSuccessful());
        self::assertEmpty($result->toArray());
    }

    public function testGetSettings(): void
    {
        // arrange
        $settings = new AdyenGivingSettings(
            true,
            'CHARITY_NAME',
            'description',
            'account',
            [2, 3, 4],
            'website',
            'logo',
            'image'
        );
        StoreContext::doWithStore('store1', [$this->givingSettingsService, 'saveAdyenGivingSettings'], [$settings]);
        $settings = new ConnectionSettings(
            'store1',
            'test',
            new ConnectionData('1234567890', '', '', 'clientKey'),
            null
        );
        StoreContext::doWithStore('store1', [$this->connectionRepository, 'setConnectionSettings'], [$settings]);
        $donationData = new DonationsData('TEST', '01234567', '012345678', 'scheme');
        StoreContext::doWithStore('store1', [$this->dataRepository, 'saveDonationsData'], [$donationData]);

        // act
        $result = CheckoutAPI::get()->donation('store1')->getDonationSettings('TEST', '1');

        // assert
        self::assertTrue($result->isSuccessful());
        self::assertEquals(
            [
                'clientKey' => 'clientKey',
                'environment' => 'test',
                'paymentMethodsConfiguration' =>
                    [
                        'donation' => [
                            'backgroundUrl' => 'image',
                            'description' => 'description',
                            'logoUrl' => 'logo',
                            'name' => 'CHARITY_NAME',
                            'url' => 'website',
                            'amounts' => [
                                'currency' => 'EUR',
                                'values' => [200, 300, 400],
                            ]
                        ]
                    ]
            ],
            $result->toArray()
        );
    }
}
