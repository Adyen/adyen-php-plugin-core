<?php

namespace Adyen\Core\BusinessLogic\AdyenAPI\PartialPayments\Requests;

use Adyen\Core\BusinessLogic\AdyenAPI\Http\Requests\HttpRequest;
use Adyen\Core\BusinessLogic\Domain\PartialPayments\Models\BalanceRequest;

/**
 * Class BalanceHttpRequest
 *
 * @package Adyen\Core\BusinessLogic\AdyenAPI\PartialPayments\Requests
 */
class BalanceHttpRequest extends HttpRequest
{
    /**
     * @var BalanceRequest
     */
    private $request;

    /**
     * @param BalanceRequest $request
     */
    public function __construct(BalanceRequest $request)
    {
        $this->request = $request;

        parent::__construct('/paymentMethods/balance', $this->transformBody());
    }

    public function transformBody(): array
    {
        return [
            'paymentMethod' => $this->request->getPaymentMethod(),
            'amount' => [
                'value' => $this->request->getAmount()->getValue(),
                'currency' => (string)$this->request->getAmount()->getCurrency(),
            ],
            'merchantAccount' => $this->request->getMerchantAccount(),
        ];
    }
}
