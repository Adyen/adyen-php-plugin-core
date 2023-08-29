<?php

namespace Adyen\Core\BusinessLogic\CheckoutAPI\CheckoutConfig\Request;

/**
 * Class DisableStoredDetailsRequest
 *
 * @package Adyen\Core\BusinessLogic\CheckoutAPI\CheckoutConfig\Request
 */
class DisableStoredDetailsRequest
{
    /**
     * @var string
     */
    private $shopperReference;
    /**
     * @var string
     */
    private $detailsReference;

    /**
     * @param string $shopperReference
     * @param string $detailsReference
     */
    public function __construct(string $shopperReference, string $detailsReference)
    {
        $this->shopperReference = $shopperReference;
        $this->detailsReference = $detailsReference;
    }

    /**
     * @return string
     */
    public function getShopperReference(): string
    {
        return $this->shopperReference;
    }

    /**
     * @return string
     */
    public function getDetailsReference(): string
    {
        return $this->detailsReference;
    }
}
