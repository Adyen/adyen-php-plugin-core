<?php

namespace Adyen\Core\BusinessLogic\AdyenAPI\Recurring\StoredDetails\Requests;

use Adyen\Core\BusinessLogic\AdyenAPI\Http\Requests\HttpRequest;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\ShopperReference;

/**
 * Class DisableStoredDetailsRequest
 *
 * @package Adyen\Core\BusinessLogic\AdyenAPI\Recurring\StoredDetails\Requests
 */
class DisableStoredDetailsRequest extends HttpRequest
{
    /**
     * @var ShopperReference
     */
    private $shopperReference;
    /**
     * @var string
     */
    private $detailReference;
    /**
     * @var string
     */
    private $merchant;

    /**
     * @param ShopperReference $shopperReference
     * @param string $detailReference
     * @param string $merchant
     */
    public function __construct(ShopperReference $shopperReference, string $detailReference, string $merchant)
    {
        $this->shopperReference = $shopperReference;
        $this->detailReference = $detailReference;
        $this->merchant = $merchant;

        parent::__construct('/disable', $this->transformBody());
    }

    /**
     * @return array
     */
    public function transformBody(): array
    {
        return [
            'shopperReference' => (string)$this->shopperReference,
            'recurringDetailReference' => $this->detailReference,
            'merchantAccount' => $this->merchant,
        ];
    }
}
