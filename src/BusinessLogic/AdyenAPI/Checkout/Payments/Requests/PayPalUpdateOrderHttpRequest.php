<?php

namespace Adyen\Core\BusinessLogic\AdyenAPI\Checkout\Payments\Requests;

use Adyen\Core\BusinessLogic\AdyenAPI\Http\Requests\HttpRequest;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\PayPalUpdateOrderRequest;

/**
 * Class PayPalUpdateOrderHttpRequest
 *
 * @package Adyen\Core\BusinessLogic\AdyenAPI\Checkout\Payments\Requests
 */
class PayPalUpdateOrderHttpRequest extends HttpRequest
{
    /**
     * @var PayPalUpdateOrderRequest
     */
    private $request;

    public function __construct(PayPalUpdateOrderRequest $request)
    {
        $this->request = $request;

        parent::__construct('/paypal/updateOrder', $this->transformBody());
    }

    public function transformBody(): array
    {
        return [
            'pspReference' => $this->request->getPspReference(),
            'paymentData' => $this->request->getPaymentData(),
            'amount' => [
                'currency' => $this->request->getAmount()->getCurrency(),
                'value' => $this->request->getAmount()->getValue(),
            ],
        ];
    }
}
