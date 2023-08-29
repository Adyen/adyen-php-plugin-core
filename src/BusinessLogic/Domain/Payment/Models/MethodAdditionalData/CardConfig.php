<?php

namespace Adyen\Core\BusinessLogic\Domain\Payment\Models\MethodAdditionalData;

use Adyen\Core\BusinessLogic\Domain\Payment\Exceptions\DuplicatedValuesNotAllowedException;
use Adyen\Core\BusinessLogic\Domain\Payment\Exceptions\InvalidCardConfigurationException;
use Adyen\Core\BusinessLogic\Domain\Payment\Exceptions\NegativeValuesNotAllowedException;
use Adyen\Core\BusinessLogic\Domain\Payment\Exceptions\StringValuesNotAllowedException;
use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class CardConfig
 *
 * @package Adyen\Core\BusinessLogic\Domain\Payment\Models\MethodAdditionalData
 */
class CardConfig implements PaymentMethodAdditionalData
{
    public const INSTALLMENT_COUNTRIES = ['BR', 'MX', 'JP', 'TK'];
    public const ANY = ['ANY'];

    /**
     * @var bool
     */
    private $showLogos;
    /**
     * @var bool
     */
    private $singleClickPayment;
    /**
     * @var bool
     */
    private $installments;
    /**
     * @var bool
     */
    private $installmentAmounts;
    /**
     * @var bool
     */
    private $sendBasket;
    /**
     * @var string[]
     */
    private $installmentCountries;
    /**
     * @var float
     */
    private $minimumAmount;
    /**
     * @var int[]
     */
    private $numberOfInstallments;

    /**
     * @param bool $showLogos
     * @param bool $singleClickPayment
     * @param bool $installments
     * @param bool $installmentAmounts
     * @param bool $sendBasket
     * @param string[] $installmentCountries
     * @param float $minimumAmount
     * @param int[] $numberOfInstallments
     *
     * @throws DuplicatedValuesNotAllowedException
     * @throws NegativeValuesNotAllowedException
     * @throws StringValuesNotAllowedException
     * @throws InvalidCardConfigurationException
     */
    public function __construct(
        bool  $showLogos = false,
        bool  $singleClickPayment = false,
        bool  $installments = false,
        bool  $installmentAmounts = false,
        bool  $sendBasket = false,
        array $installmentCountries = [],
        float $minimumAmount = 0,
        array $numberOfInstallments = []
    )
    {
        $this->validateNumberOfInstallments($numberOfInstallments);

        if ($installmentCountries === self::ANY) {
            $installmentCountries = self::INSTALLMENT_COUNTRIES;
        }

        if (!$installments && $installmentAmounts) {
            throw new InvalidCardConfigurationException(
                new TranslatableLabel(
                    'Invalid card configuration.',
                    'payments.invalidCardConfig'
                )
            );
        }

        $this->showLogos = $showLogos;
        $this->singleClickPayment = $singleClickPayment;
        $this->installments = $installments;
        $this->installmentAmounts = $installmentAmounts;
        $this->sendBasket = $sendBasket;
        $this->installmentCountries = $installmentCountries;
        $this->minimumAmount = $minimumAmount;
        $this->numberOfInstallments = array_map('intval' , $numberOfInstallments);
    }

    /**
     * @return bool
     */
    public function isShowLogos(): bool
    {
        return $this->showLogos;
    }

    /**
     * @return bool
     */
    public function isSingleClickPayment(): bool
    {
        return $this->singleClickPayment;
    }

    /**
     * @return bool
     */
    public function isInstallments(): bool
    {
        return $this->installments;
    }

    /**
     * @return bool
     */
    public function isInstallmentAmounts(): bool
    {
        return $this->installmentAmounts;
    }

    /**
     * @return bool
     */
    public function isSendBasket(): bool
    {
        return $this->sendBasket;
    }

    /**
     * @return string[]
     */
    public function getInstallmentCountries(): array
    {
        return $this->installmentCountries;
    }

    /**
     * @return float
     */
    public function getMinimumAmount(): float
    {
        return $this->minimumAmount;
    }

    /**
     * @return int[]
     */
    public function getNumberOfInstallments(): array
    {
        return $this->numberOfInstallments;
    }

    /**
     * @param array $instalments
     *
     * @return void
     *
     * @throws StringValuesNotAllowedException
     * @throws NegativeValuesNotAllowedException
     * @throws DuplicatedValuesNotAllowedException
     */
    private function validateNumberOfInstallments(array $instalments): void {

        foreach ($instalments as $value) {
            if (!is_numeric($value) || (is_string($value) && !ctype_digit($value))) {
                throw new StringValuesNotAllowedException('Each element must be number.');
            }
        }

        if (count($instalments) && min($instalments) <= 0) {
            throw new NegativeValuesNotAllowedException('Each element must be a positive number.');
        }

        if (count(array_unique($instalments)) !== count($instalments)) {
            throw new DuplicatedValuesNotAllowedException('Duplicated amounts are not allowed');
        }
    }
}
