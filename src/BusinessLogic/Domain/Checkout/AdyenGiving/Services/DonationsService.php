<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\AdyenGiving\Services;

use Adyen\Core\BusinessLogic\Domain\AdyenGivingSettings\Services\AdyenGivingSettingsService;
use Adyen\Core\BusinessLogic\Domain\Checkout\AdyenGiving\Models\DonationCheckoutConfig;
use Adyen\Core\BusinessLogic\Domain\Checkout\AdyenGiving\Models\DonationRequest;
use Adyen\Core\BusinessLogic\Domain\Checkout\AdyenGiving\Proxies\DonationsProxy;
use Adyen\Core\BusinessLogic\Domain\Checkout\AdyenGiving\Repositories\DonationsDataRepository;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Exceptions\InvalidCurrencyCode;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Currency;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PaymentMethodCode;
use Adyen\Core\BusinessLogic\Domain\Connection\Enums\Mode;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFountException;
use Adyen\Core\BusinessLogic\Domain\Connection\Models\ConnectionSettings;
use Adyen\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use Adyen\Core\BusinessLogic\Domain\Integration\Order\OrderService;
use Adyen\Core\BusinessLogic\Domain\Integration\Webhook\WebhookUrlService;
use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Exception;

/**
 * Class DonationsService
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\AdyenGiving\Services
 */
class DonationsService
{
    /**
     * @var DonationsProxy
     */
    protected $proxy;
    /**
     * @var AdyenGivingSettingsService
     */
    protected $adyenGivingService;
    /**
     * @var ConnectionService
     */
    protected $connectionService;
    /**
     * @var DonationsDataRepository
     */
    protected $donationsDataRepository;
    /**
     * @var OrderService
     */
    protected $orderService;
    /**
     * @var WebhookUrlService
     */
    protected $webhookUrlService;

    /**
     * @param  DonationsProxy  $proxy
     * @param  AdyenGivingSettingsService  $adyenGivingService
     * @param  ConnectionService  $connectionService
     * @param  DonationsDataRepository  $donationsDataRepository
     * @param  OrderService  $orderService
     * @param  WebhookUrlService  $webhookUrlService
     */
    public function __construct(
        DonationsProxy $proxy,
        AdyenGivingSettingsService $adyenGivingService,
        ConnectionService $connectionService,
        DonationsDataRepository $donationsDataRepository,
        OrderService $orderService,
        WebhookUrlService $webhookUrlService
    ) {
        $this->proxy                   = $proxy;
        $this->adyenGivingService      = $adyenGivingService;
        $this->connectionService       = $connectionService;
        $this->donationsDataRepository = $donationsDataRepository;
        $this->orderService            = $orderService;
        $this->webhookUrlService       = $webhookUrlService;
    }


    /**
     * @param  string  $merchantReference
     * @param  string  $currencyFactor
     *
     * @return DonationCheckoutConfig|null
     *
     * @throws ConnectionSettingsNotFountException
     * @throws InvalidCurrencyCode
     * @throws Exception
     */
    public function getDonationSettings(string $merchantReference, string $currencyFactor): ?DonationCheckoutConfig
    {
        $givingSettings = $this->adyenGivingService->getAdyenGivingSettings();

        if ( ! $givingSettings || ! $givingSettings->isEnableAdyenGiving()) {
            return null;
        }

        $connectionSettings = $this->getConnectionSettings();
        $donationsData      = $this->donationsDataRepository->getDonationsData($merchantReference);

        if ( ! $donationsData) {
            return null;
        }

        $currency = $this->orderService->getOrderCurrency($merchantReference);

        return new DonationCheckoutConfig(
            $connectionSettings->getActiveConnectionData()->getClientKey(),
            $connectionSettings->getMode(),
            $givingSettings->getBackgroundImage(),
            $givingSettings->getCharityDescription(),
            $givingSettings->getLogo(),
            $givingSettings->getCharityName(),
            $givingSettings->getCharityWebsite(),
            $currency,
            array_map(static function ($amount) use ($currency, $currencyFactor): Amount {
                return Amount::fromFloat($amount * (float) $currencyFactor, Currency::fromIsoCode($currency));
            }, $givingSettings->getDonationAmounts())
        );
    }

    /**
     * @param  Amount  $amount
     * @param  string  $merchantReference
     *
     * @return string
     *
     * @throws ConnectionSettingsNotFountException
     * @throws Exception
     */
    public function makeDonation(Amount $amount, string $merchantReference): string
    {
        $givingSettings = $this->adyenGivingService->getAdyenGivingSettings();

        if ( ! $givingSettings || ! $givingSettings->isEnableAdyenGiving()) {
            return '';
        }

        $donationData = $this->donationsDataRepository->getDonationsData($merchantReference);

        if ( ! $donationData) {
            return '';
        }

        $connectionSettings = $this->getConnectionSettings();
        $this->donationsDataRepository->deleteDonationsData($merchantReference);
        $request = new DonationRequest(
            $donationData->getDonationToken(),
            $amount,
            $donationData->getPaymentMethod() === (string)PaymentMethodCode::scheme() ?
                $donationData->getPaymentMethod() : (string)PaymentMethodCode::sepa(),
            $donationData->getPspReference(),
            $givingSettings->getCharityMerchantAccount(),
            $connectionSettings->getActiveConnectionData()->getMerchantId(),
            $this->webhookUrlService->getWebhookUrl()
        );

        return $this->proxy->makeDonation($request);
    }

    /**
     * @return ConnectionSettings
     *
     * @throws ConnectionSettingsNotFountException
     */
    private function getConnectionSettings(): ConnectionSettings
    {
        $connectionSettings = $this->connectionService->getConnectionData();

        if ($connectionSettings === null) {
            throw new ConnectionSettingsNotFountException(
                new TranslatableLabel('Connection settings not found.', 'connection.settingsNotFound')
            );
        }

        return $connectionSettings;
    }
}
