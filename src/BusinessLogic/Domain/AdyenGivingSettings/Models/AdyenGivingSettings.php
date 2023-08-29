<?php

namespace Adyen\Core\BusinessLogic\Domain\AdyenGivingSettings\Models;

use Adyen\Core\BusinessLogic\Domain\AdyenGivingSettings\Exceptions\DuplicatedValuesNotAllowedException;
use Adyen\Core\BusinessLogic\Domain\AdyenGivingSettings\Exceptions\NegativeValuesNotAllowedException;
use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class AdyenGivingSettings
 *
 * @package Adyen\Core\BusinessLogic\Domain\AdyenGivingSettings\Models
 */
class AdyenGivingSettings
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
     * @var array
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

    /**
     * @param bool $enableAdyenGiving
     * @param string $charityName
     * @param string $charityDescription
     * @param string $charityMerchantAccount
     * @param array $donationAmounts
     * @param string $charityWebsite
     * @param string $logo
     * @param string $backgroundImage
     *
     * @throws DuplicatedValuesNotAllowedException
     * @throws NegativeValuesNotAllowedException
     */
    public function __construct(
        bool $enableAdyenGiving = true,
        string $charityName = '',
        string $charityDescription = '',
        string $charityMerchantAccount = '',
        array $donationAmounts = [],
        string $charityWebsite = '',
        string $logo = '',
        string $backgroundImage = ''
    ) {
        $this->validateDonationsArray($donationAmounts);

        $this->enableAdyenGiving      = $enableAdyenGiving;
        $this->charityName            = $charityName;
        $this->charityDescription     = $charityDescription;
        $this->charityMerchantAccount = $charityMerchantAccount;
        $this->donationAmounts        = $donationAmounts;
        $this->charityWebsite         = $charityWebsite;
        $this->logo                   = $logo;
        $this->backgroundImage        = $backgroundImage;
    }

    /**
     * @return bool
     */
    public function isEnableAdyenGiving(): bool
    {
        return $this->enableAdyenGiving;
    }

    /**
     * @return string
     */
    public function getCharityName(): string
    {
        return $this->charityName;
    }

    /**
     * @return string
     */
    public function getCharityDescription(): string
    {
        return $this->charityDescription;
    }

    /**
     * @return string
     */
    public function getCharityMerchantAccount(): string
    {
        return $this->charityMerchantAccount;
    }

    /**
     * @return array
     */
    public function getDonationAmounts(): array
    {
        return $this->donationAmounts;
    }

    /**
     * @return string
     */
    public function getCharityWebsite(): string
    {
        return $this->charityWebsite;
    }

    /**
     * @return string
     */
    public function getLogo(): string
    {
        return $this->logo;
    }

    /**
     * @return string
     */
    public function getBackgroundImage(): string
    {
        return $this->backgroundImage;
    }

    /**
     * @param array $donations
     *
     * @return void
     *
     * @throws NegativeValuesNotAllowedException
     * @throws DuplicatedValuesNotAllowedException
     */
    private function validateDonationsArray(array $donations): void
    {
        if (empty($donations)) {
            return;
        }

        if (count($donations) && min($donations) <= 0) {
            throw new NegativeValuesNotAllowedException('Each amount must be a positive number.');
        }

        if (count(array_unique($donations)) !== count($donations)) {
            throw new DuplicatedValuesNotAllowedException('Duplicated amounts are not allowed');
        }
    }
}
