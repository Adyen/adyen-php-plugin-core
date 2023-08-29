<?php

namespace Adyen\Core\BusinessLogic\AdyenAPI\Refund\Requests;

use Adyen\Core\BusinessLogic\AdyenAPI\Http\Requests\HttpRequest;
use Adyen\Core\BusinessLogic\Domain\Refund\Models\RefundRequest;

/**
 * Class RefundHttpRequest
 *
 * @package Adyen\Core\BusinessLogic\AdyenAPI\Refund\Requests
 */
class RefundHttpRequest extends HttpRequest
{
    /**
     * @var RefundRequest
     */
    private $refundRequest;

    /**
     * @param RefundRequest $refundRequest
     */
    public function __construct(RefundRequest $refundRequest)
    {
        $this->refundRequest = $refundRequest;

        parent::__construct('/payments/' . $refundRequest->getPspReference() . '/refunds', $this->transformBody());
    }

    /**
     * @return array
     */
    private function transformBody(): array
    {
        return [
            'merchantAccount' => $this->refundRequest->getMerchantAccount(),
            'amount' => [
                'currency' => $this->refundRequest->getAmount()->getCurrency()->getIsoCode(),
                'value' => $this->refundRequest->getAmount()->getValue(),
            ]
        ];
    }
}
