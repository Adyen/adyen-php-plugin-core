<?php

namespace Adyen\Core\BusinessLogic\CheckoutAPI\Donations\Response;

use Adyen\Core\BusinessLogic\AdminAPI\Response\Response;
use Adyen\Core\BusinessLogic\Domain\Checkout\AdyenGiving\Models\DonationCheckoutConfig;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\Amount\Amount;

/**
 * Class DonationSettingsResponse
 *
 * @package Adyen\Core\BusinessLogic\CheckoutAPI\Donations\Response
 */
class DonationSettingsResponse extends Response
{
    /**
     * @var DonationCheckoutConfig
     */
    private $config;

    /**
     * @param DonationCheckoutConfig|null $config
     */
    public function __construct(?DonationCheckoutConfig $config)
    {
        $this->config = $config;
    }

    public function toArray(): array
    {
        if (!$this->config) {
            return [];
        }

        return [
            'clientKey' => $this->config->getClientKey(),
            'environment' => $this->config->getEnvironment(),
            'paymentMethodsConfiguration' => [
                'donation' => [
                    'backgroundUrl' => $this->config->getBackgroundUrl(),
                    'description' => $this->config->getDescription(),
                    'logoUrl' => $this->config->getLogoUrl(),
                    'name' => $this->config->getName(),
                    'url' => $this->config->getUrl(),
                    'amounts' => [
                        'currency' => $this->config->getCurrency(),
                        'values' => array_map(static function (Amount $amount): int {
                            return $amount->getValue();
                        }, $this->config->getAmounts()),
                    ]
                ]
            ]
        ];
    }
}
