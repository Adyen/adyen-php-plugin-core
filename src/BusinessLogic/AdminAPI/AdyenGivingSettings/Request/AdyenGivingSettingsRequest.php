<?php

namespace Adyen\Core\BusinessLogic\AdminAPI\AdyenGivingSettings\Request;

use Adyen\Core\BusinessLogic\AdminAPI\Request\Request;
use Adyen\Core\BusinessLogic\Domain\AdyenGivingSettings\Models\AdyenGivingSettings;

/**
 * Class AdyenGivingSettingsRequest
 *
 * @package Adyen\Core\BusinessLogic\AdminAPI\AdyenGivingSettings\Request
 */
class AdyenGivingSettingsRequest extends Request
{
    /**
     * @var bool
     */
    private $enableAdyenGiving;

    /**
     * @var string
     */
    private $charityName;

    /**
     * @var string
     */
    private $charityDescription;

    /**
     * @var string
     */
    private $charityMerchantAccount;

    /**
     * @var string
     */
    private $donationAmounts;

    /**
     * @var string
     */
    private $charityWebsite;

    /**
     * @var string
     */
    private $logo;

    /**
     * @var string
     */
    private $backgroundImage;

    public function __construct(
        bool $enableAdyenGiving,
        string $charityName = '',
        string $charityDescription = '',
        string $charityMerchantAccount = '',
        string $donationAmounts = '',
        string $charityWebsite = '',
        string $logo = '',
        string $backgroundImage = ''
    ) {
        $this->enableAdyenGiving = $enableAdyenGiving;
        $this->charityName = $charityName;
        $this->charityDescription = $charityDescription;
        $this->charityMerchantAccount = $charityMerchantAccount;
        $this->donationAmounts = $donationAmounts;
        $this->charityWebsite = $charityWebsite;
        $this->logo = $logo;
        $this->backgroundImage = $backgroundImage;
    }

    /**
     * @return AdyenGivingSettings
     */
    public function transformToDomainModel(): object
    {
        $donationAmounts = $this->donationAmounts ? array_map(static function (string $amount) {
            return (float)trim($amount);
        }, explode(',', $this->donationAmounts)) : [];

        return new AdyenGivingSettings(
            $this->enableAdyenGiving,
            $this->charityName,
            $this->charityDescription,
            $this->charityMerchantAccount,
            $donationAmounts,
            $this->charityWebsite,
            $this->logo,
            $this->backgroundImage
        );
    }
}
