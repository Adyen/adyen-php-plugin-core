<?php

namespace Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\AdditionalData;

/**
 * Class AdditionalData
 *
 * @package Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\AdditionalData
 */
class AdditionalData
{
    /**
     * @var RiskData|null
     */
    private $riskData;
    /**
     * @var EnhancedSchemeData|null
     */
    private $enhancedSchemeData;
    /**
     * @var bool
     */
    private $manualCapture;

    /**
     * @param RiskData|null $riskData
     * @param EnhancedSchemeData|null $enhancedSchemeData
     * @param bool|null $manualCapture
     */
    public function __construct(?RiskData $riskData = null, EnhancedSchemeData $enhancedSchemeData = null, ?bool $manualCapture = null)
    {
        $this->riskData = $riskData;
        $this->enhancedSchemeData = $enhancedSchemeData;
        $this->manualCapture = $manualCapture;
    }

    /**
     * @return RiskData|null
     */
    public function getRiskData(): ?RiskData
    {
        return $this->riskData;
    }

    /**
     * @return EnhancedSchemeData|null
     */
    public function getEnhancedSchemeData(): ?EnhancedSchemeData
    {
        return $this->enhancedSchemeData;
    }

    /**
     * @return bool|null
     */
    public function getManualCapture(): ?bool
    {
        return $this->manualCapture;
    }
}
