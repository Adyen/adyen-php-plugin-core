<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\AdyenGivingSettings\Response;

use Adyen\Core\BusinessLogic\AdminAPI\Response\Response;
use Adyen\Core\BusinessLogic\Domain\AdyenGivingSettings\Models\AdyenGivingSettings;

/**
 * Class AdyenGivingSettingsGetResponse
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\AdyenGivingSettings\Response
 */
class AdyenGivingSettingsGetResponse extends Response
{
    /**
     * @var AdyenGivingSettings
     */
    private $adyenGivingSettings;

    /**
     * @param AdyenGivingSettings|null $adyenGivingSettings
     */
    public function __construct(?AdyenGivingSettings $adyenGivingSettings)
    {
        $this->adyenGivingSettings = $adyenGivingSettings;
    }

    /**
     * Returns array representation of AdyenGivingSettings.
     *
     * @return array Array representation of AdyenGivingSettings.
     */
    public function toArray(): array
    {
        return $this->adyenGivingSettings ? $this->transformAdyenGivingSettings() : [];
    }

    /**
     * @return array
     */
    private function transformAdyenGivingSettings(): array
    {
        return [
                'enableAdyenGiving' => $this->adyenGivingSettings->isEnableAdyenGiving(),
                'charityName' => $this->adyenGivingSettings->getCharityName(),
                'charityDescription' => $this->adyenGivingSettings->getCharityDescription(),
                'charityMerchantAccount' => $this->adyenGivingSettings->getCharityMerchantAccount(),
                'donationAmount' => empty($this->adyenGivingSettings->getDonationAmounts()) ? '' : implode(
                        ",",
                        $this->adyenGivingSettings->getDonationAmounts()
                ),
                'charityWebsite' => $this->adyenGivingSettings->getCharityWebsite(),
                'logo' => $this->adyenGivingSettings->getLogo(),
                'backgroundImage' => $this->adyenGivingSettings->getBackgroundImage()
        ];
    }
}
