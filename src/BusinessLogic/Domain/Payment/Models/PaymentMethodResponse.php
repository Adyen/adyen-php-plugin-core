<?php

namespace Adyen\Core\BusinessLogic\Domain\Payment\Models;

use Adyen\Core\BusinessLogic\Domain\Payment\Exceptions\PaymentMethodRequestDataEmptyException;
use Adyen\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class PaymentMethodResponse
 *
 * @package Adyen\Core\BusinessLogic\Domain\Payment\Models
 */
class PaymentMethodResponse
{
    /**
     * @var string
     */
    private $methodId;
    /**
     * @var string
     */
    private $code;
    /**
     * @var bool
     */
    private $enabled;
    /**
     * @var string[]
     */
    private $countries;
    /**
     * @var string[]
     */
    private $currencies;
    /**
     * @var string
     */
    private $logo;
    /**
     * @var string
     */
    private $type;
    /**
     * @var string
     */
    private $name;

    /**
     * @param string $methodId
     * @param string $code
     * @param bool $enabled
     * @param string[] $countries
     * @param string[] $currencies
     * @param string $logo
     * @param string $type
     * @param string $name
     *
     * @throws PaymentMethodRequestDataEmptyException
     */
    public function __construct(
        string $methodId,
        string $code,
        bool   $enabled,
        array  $countries = [],
        array  $currencies = [],
        string $logo = '',
        string $type = '',
        string $name = ''
    )
    {
        if (empty($methodId) || empty($code)) {
            throw new PaymentMethodRequestDataEmptyException(
                new TranslatableLabel('Method id and code fields are required.', 'payments.requiredFieldsError')
            );
        }

        $this->methodId = $methodId;
        $this->code = $code;
        $this->enabled = $enabled;
        $this->countries = $countries;
        $this->currencies = $currencies;
        $this->logo = $logo;
        $this->type = $type;
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getMethodId(): string
    {
        return $this->methodId;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @return string[]
     */
    public function getCountries(): array
    {
        return $this->countries;
    }

    /**
     * @return string[]
     */
    public function getCurrencies(): array
    {
        return $this->currencies;
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
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $logo
     */
    public function setLogo(string $logo): void
    {
        $this->logo = $logo;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
