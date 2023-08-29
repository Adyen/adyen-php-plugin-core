<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models;

/**
 * Class RiskData
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models
 */
class RiskData
{
    /**
     * @var string
     */
    private $clientData;
    /**
     * @var string[]
     */
    private $customFields;
    /**
     * @var int
     */
    private $fraudOffset;
    /**
     * @var string
     */
    private $profileReference;

    /**
     * @param string $clientData
     * @param string[] $customFields
     * @param int $fraudOffset
     * @param string $profileReference
     */
    public function __construct(string $clientData, array $customFields, int $fraudOffset, string $profileReference)
    {
        $this->clientData = $clientData;
        $this->customFields = $customFields;
        $this->fraudOffset = $fraudOffset;
        $this->profileReference = $profileReference;
    }

    /**
     * @return string
     */
    public function getClientData(): string
    {
        return $this->clientData;
    }

    /**
     * @return string[]
     */
    public function getCustomFields(): array
    {
        return $this->customFields;
    }

    /**
     * @return int
     */
    public function getFraudOffset(): int
    {
        return $this->fraudOffset;
    }

    /**
     * @return string
     */
    public function getProfileReference(): string
    {
        return $this->profileReference;
    }
}
