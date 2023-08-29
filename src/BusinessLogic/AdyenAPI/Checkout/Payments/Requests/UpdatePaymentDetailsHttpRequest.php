<?php

namespace Adyen\Core\BusinessLogic\AdyenAPI\Checkout\Payments\Requests;

use Adyen\Core\BusinessLogic\AdyenAPI\Http\Requests\HttpRequest;
use Adyen\Core\BusinessLogic\Domain\Checkout\PaymentRequest\Models\UpdatePaymentDetailsRequest;

/**
 * Class UpdatePaymentDetailsHttpRequest
 *
 * @package Adyen\Core\BusinessLogic\AdyenAPI\Checkout\Payments\Requests
 */
class UpdatePaymentDetailsHttpRequest extends HttpRequest
{
    /**
     * @var UpdatePaymentDetailsRequest
     */
    private $request;

    public function __construct(UpdatePaymentDetailsRequest $request)
    {
        $this->request = $request;

        parent::__construct('/payments/details', $this->transformBody());
    }

    private function transformBody(): array
    {
        $body = [
            'details' => $this->request->getDetails()
        ];
        if (!empty($this->request->getPaymentData())) {
            $body['paymentData'] = $this->request->getPaymentData();
        }

        return $body;
    }
}
