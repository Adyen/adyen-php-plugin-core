<?php

namespace Adyen\Core\BusinessLogic\CheckoutAPI\CheckoutConfig\Controller;

use Adyen\Core\BusinessLogic\CheckoutAPI\CheckoutConfig\Request\DisableStoredDetailsRequest;
use Adyen\Core\BusinessLogic\CheckoutAPI\CheckoutConfig\Request\PaymentCheckoutConfigRequest;
use Adyen\Core\BusinessLogic\CheckoutAPI\CheckoutConfig\Response\DisableStoredDetailsResponse;
use Adyen\Core\BusinessLogic\CheckoutAPI\CheckoutConfig\Response\PaymentCheckoutConfigResponse;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\ShopperReference;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Services\PaymentCheckoutConfigService;
use Adyen\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFountException;

/**
 * Class CheckoutConfigController
 *
 * @package Adyen\Core\BusinessLogic\CheckoutAPI\CheckoutConfig\Controller
 */
class CheckoutConfigController
{
    /**
     * @var PaymentCheckoutConfigService
     */
    private $service;

    public function __construct(PaymentCheckoutConfigService $service)
    {
        $this->service = $service;
    }

    public function getPaymentCheckoutConfig(PaymentCheckoutConfigRequest $request): PaymentCheckoutConfigResponse
    {
        return new PaymentCheckoutConfigResponse(
            $this->service->getPaymentCheckoutConfig(
                $request->getAmount(),
                $request->getCountry(),
                $request->getShopperLocale(),
                $request->getShopperReference() ? ShopperReference::parse($request->getShopperReference()) : null
            ),
            $request->getAmount(),
            $request->getShopperLocale(),
            $request->getCountry() ? $request->getCountry()->getIsoCode() : ''
        );
    }

    public function getExpressPaymentCheckoutConfig(PaymentCheckoutConfigRequest $request): PaymentCheckoutConfigResponse
    {
        return new PaymentCheckoutConfigResponse(
            $this->service->getExpressPaymentCheckoutConfig(
                $request->getAmount(),
                $request->getCountry(),
                $request->getShopperLocale(),
                $request->getShopperReference() ? ShopperReference::parse($request->getShopperReference()) : null
            ),
            $request->getAmount(),
            $request->getShopperLocale(),
            $request->getCountry() ? $request->getCountry()->getIsoCode() : ''
        );
    }

    /**
     * @param DisableStoredDetailsRequest $request
     *
     * @return DisableStoredDetailsResponse
     *
     * @throws ConnectionSettingsNotFountException
     */
    public function disableStoredDetails(DisableStoredDetailsRequest $request): DisableStoredDetailsResponse
    {
        $this->service->disableStoredPaymentDetails(
            ShopperReference::parse($request->getShopperReference()),
            $request->getDetailsReference()
        );

        return new DisableStoredDetailsResponse();
    }
}
