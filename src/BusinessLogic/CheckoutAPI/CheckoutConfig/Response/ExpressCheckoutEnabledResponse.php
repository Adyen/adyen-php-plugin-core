<?php

namespace Adyen\Core\BusinessLogic\CheckoutAPI\CheckoutConfig\Response;

use Adyen\Core\BusinessLogic\AdminAPI\Response\Response;

/**
 * Class ExpressCheckoutEnabledResponse
 *
 * @package Adyen\Core\BusinessLogic\CheckoutAPI\CheckoutConfig\Response
 */
class ExpressCheckoutEnabledResponse extends Response
{
    /**
     * @var bool
     */
    private $enabled;

    /**
     * @param bool $enabled
     */
    public function __construct(bool $enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return ['isEnabled' => $this->enabled];
    }
}
