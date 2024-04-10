<?php

namespace Adyen\Core\BusinessLogic\AdyenAPI\AuthorizationAdjustment\Requests;

use Adyen\Core\BusinessLogic\AdyenAPI\Http\Requests\HttpRequest;
use Adyen\Core\BusinessLogic\Domain\AuthorizationAdjustment\Models\AuthorizationAdjustmentRequest;

/**
 * Class AuthorizationAdjustmentHttpRequest.
 *
 * @package Adyen\Core\BusinessLogic\AdyenAPI\AuthorizationAdjustment\Requests
 */
class AuthorizationAdjustmentHttpRequest extends HttpRequest
{
    /**
     * @var AuthorizationAdjustmentRequest
     */
    private $authorizationAdjustmentRequest;

    /**
     * @param AuthorizationAdjustmentRequest $authorizationAdjustmentRequest
     */
    public function __construct(AuthorizationAdjustmentRequest $authorizationAdjustmentRequest)
    {
        $this->authorizationAdjustmentRequest = $authorizationAdjustmentRequest;

        parent::__construct('/payments/' . $authorizationAdjustmentRequest->getPspReference() . '/amountUpdates', $this->transformBody());
    }

    /**
     * @return array
     */
    private function transformBody(): array
    {
        return [
            'merchantAccount' => $this->authorizationAdjustmentRequest->getMerchantAccount(),
            'amount' => [
                'currency' => $this->authorizationAdjustmentRequest->getAmount()->getCurrency()->getIsoCode(),
                'value' => $this->authorizationAdjustmentRequest->getAmount()->getValue(),
            ],
            'reason' => $this->authorizationAdjustmentRequest->getReason(),
            'reference' => $this->authorizationAdjustmentRequest->getMerchantReference()
        ];
    }
}
