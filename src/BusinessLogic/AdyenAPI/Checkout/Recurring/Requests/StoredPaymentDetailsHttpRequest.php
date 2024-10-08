<?php

namespace Adyen\Core\BusinessLogic\AdyenAPI\Checkout\Recurring\Requests;

use Adyen\Core\BusinessLogic\AdyenAPI\Http\Requests\HttpRequest;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\ShopperReference;

/**
 * Class StoredPaymentDetailsHttpRequest
 *
 * @package Adyen\Core\BusinessLogic\AdyenAPI\Recurring\StoredDetails\Requests
 */
class StoredPaymentDetailsHttpRequest extends HttpRequest
{
    /**
     * @var ShopperReference
     */
    private $shopperReference;
    /**
     * @var string
     */
    private $merchant;

    /**
     * @param ShopperReference $shopperReference
     * @param string $merchant
     */
    public function __construct(ShopperReference $shopperReference, string $merchant)
    {
        $this->shopperReference = $shopperReference;
        $this->merchant = $merchant;

        parent::__construct('/storedPaymentMethods', [], $this->transformQueryParameters());
    }

    /**
     * @return array
     */
    public function transformQueryParameters(): array
    {
        return [
            'shopperReference' => (string)$this->shopperReference,
            'merchantAccount' => $this->merchant,
        ];
    }
}
