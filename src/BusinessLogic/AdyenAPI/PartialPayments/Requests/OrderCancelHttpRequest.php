<?php

namespace Adyen\Core\BusinessLogic\AdyenAPI\PartialPayments\Requests;

use Adyen\Core\BusinessLogic\AdyenAPI\Http\Requests\HttpRequest;

/**
 * Class OrderCancelHttpRequest
 *
 * @package Adyen\Core\BusinessLogic\AdyenAPI\PartialPayments\Requests
 */
class OrderCancelHttpRequest extends HttpRequest
{
    private $request;

    /**
     * @param $request
     */
    public function __construct($request)
    {
        $this->request = $request;

        parent::__construct('/orders/cancel', $this->transformBody());
    }

    public function transformBody(): array
    {
        return [
            'order' => [
                'pspReference' => $this->request->getOrder()->getPspReference(),
                'orderData' => $this->request->getOrder()->getOrderData(),
            ],
            'merchantAccount' => $this->request->getMerchantAccount(),
        ];
    }
}
