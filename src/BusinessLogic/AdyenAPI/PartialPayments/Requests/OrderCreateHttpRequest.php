<?php

namespace Adyen\Core\BusinessLogic\AdyenAPI\PartialPayments\Requests;

use Adyen\Core\BusinessLogic\AdyenAPI\Http\Requests\HttpRequest;
use Adyen\Core\BusinessLogic\Domain\PartialPayments\Models\OrderCreateRequest;

/**
 * Class OrderCreateHttpRequest
 *
 * @package Adyen\Core\BusinessLogic\AdyenAPI\PartialPayments\Requests
 */
class OrderCreateHttpRequest extends HttpRequest
{
    /**
     * @var OrderCreateRequest
     */
    private $request;

    /**
     * @param OrderCreateRequest $request
     */
    public function __construct(OrderCreateRequest $request)
    {
        $this->request = $request;

        parent::__construct('/orders', $this->transformBody());
    }

    public function transformBody(): array
    {
        return [
            'reference' => $this->request->getReference(),
            'amount' => [
                'value' => $this->request->getAmount()->getValue(),
                'currency' => (string)$this->request->getAmount()->getCurrency(),
            ],
            'merchantAccount' => $this->request->getMerchantAccount(),
        ];
    }
}
